"""Coordinate exclusive maintenance around the backup container in GitHub Actions."""
import json
import os
import subprocess
import time
import urllib.request


def az(*args):
    result = subprocess.run(["az", *args, "-o", "json"], capture_output=True, text=True, timeout=120)
    if result.returncode:
        raise RuntimeError("Azure operation failed; credential-bearing diagnostics suppressed")
    return json.loads(result.stdout) if result.stdout.strip() else None


group = "rg-suara-pergerakan"
app = "suara-web"
server = os.environ["DB_HOST"].split(".")[0]
rule = "backup-runner-" + os.environ["GITHUB_RUN_ID"]
changed = []
firewall_created = False
started = time.monotonic()

try:
    revisions = az("containerapp", "revision", "list", "-g", group, "-n", app)
    active = [revision["name"] for revision in revisions if revision["properties"]["active"]]
    if not active:
        raise RuntimeError("No active web revision; refusing to start maintenance")
    # No backup may race an existing migration or another SQL writer job.
    executions = az("containerapp", "job", "execution", "list", "-g", group, "-n", "suara-migration")
    if any(execution["properties"]["status"] == "Running" for execution in executions):
        raise RuntimeError("Migration is still running")
    config = az("containerapp", "show", "-g", group, "-n", app)
    os.environ["RELEASE_IMAGE"] = config["properties"]["template"]["containers"][0]["image"]
    with urllib.request.urlopen("https://api.ipify.org", timeout=30) as response:
        ip = response.read().decode().strip()
    az("sql", "server", "firewall-rule", "create", "-g", group, "-s", server, "-n", rule,
       "--start-ip-address", ip, "--end-ip-address", ip)
    firewall_created = True
    # Persist recovery state before any revision is changed, including ambiguous API failures.
    with open(os.environ["RUNNER_TEMP"] + "/backup-recovery.json", "w") as recovery:
        json.dump({"revisions": active, "server": server, "rule": rule}, recovery)
    for revision in active:
        changed.append(revision)
        az("containerapp", "revision", "deactivate", "-g", group, "-n", app, "--revision", revision)
    time.sleep(30)
    os.environ["WRITERS_STOPPED"] = "true"
    keys = [key for key in os.environ if key.startswith(("BACKUP_R2_", "R2_"))]
    keys += ["DB_HOST", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD", "RELEASE_IMAGE", "WRITERS_STOPPED", "BACKUP_MEDIA"]
    command = ["docker", "run", "--rm", "--name", "suara-backup"]
    for key in keys:
        command += ["-e", key]
    command += ["suara-operations:runner"]
    result = subprocess.run(command, timeout=1200)
    if result.returncode:
        raise RuntimeError("Backup container failed")
finally:
    # Stop the exporter before allowing application writes, including timeout paths.
    subprocess.run(["docker", "stop", "-t", "60", "suara-backup"], capture_output=True, timeout=90)
    errors = []
    for revision in changed:
        for attempt in range(3):
            try:
                az("containerapp", "revision", "activate", "-g", group, "-n", app, "--revision", revision)
                break
            except RuntimeError:
                if attempt == 2:
                    errors.append("Web recovery failed")
    if firewall_created:
        try:
            az("sql", "server", "firewall-rule", "delete", "-g", group, "-s", server, "-n", rule)
        except RuntimeError:
            errors.append("Temporary firewall cleanup failed")
    print("Maintenance duration seconds:", round(time.monotonic() - started), flush=True)
    if errors:
        raise RuntimeError("; ".join(errors))
