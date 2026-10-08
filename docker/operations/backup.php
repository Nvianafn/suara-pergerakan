<?php

// The export runner is invoked by the coordinator after all web replicas stop.
require '/app/vendor/autoload.php';

use Aws\S3\S3Client;

function required(string $key): string
{
    return getenv($key) ?: throw new RuntimeException("Missing configuration: {$key}");
}

try {
    $target = new S3Client([
        'version' => 'latest', 'region' => 'auto',
        'endpoint' => required('BACKUP_R2_ENDPOINT'), 'use_path_style_endpoint' => true,
        'credentials' => ['key' => required('BACKUP_R2_ACCESS_KEY_ID'), 'secret' => required('BACKUP_R2_SECRET_ACCESS_KEY')],
    ]);
    $bucket = required('BACKUP_R2_BUCKET');
    $prefix = 'snapshots/'.gmdate('Y/m/d/His').'-'.bin2hex(random_bytes(6));
    $file = '/tmp/database.bacpac';
    $pdo = new PDO('sqlsrv:Server='.required('DB_HOST').',1433;Database='.required('DB_DATABASE').';Encrypt=yes;TrustServerCertificate=no', required('DB_USERNAME'), required('DB_PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("DECLARE @r int; EXEC @r=sp_getapplock @Resource='suara-operations', @LockMode='Exclusive', @LockOwner='Session', @LockTimeout=0; IF @r<0 THROW 50001, 'Operation already running', 1;");

    if (getenv('WRITERS_STOPPED') !== 'true') {
        throw new RuntimeException('Coordinator must confirm writers stopped');
    }
    $writers = $pdo->query("SELECT COUNT(*) FROM sys.dm_exec_sessions WHERE database_id=DB_ID() AND session_id<>@@SPID AND is_user_process=1 AND login_name='suara_web'")->fetchColumn();
    if ((int) $writers !== 0) {
        throw new RuntimeException('Runtime database connections are still active');
    }
    $quote = fn ($s) => '"'.str_replace('"', '""', $s).'"';
    $connection = 'Server=tcp:'.required('DB_HOST').',1433;Initial Catalog='.required('DB_DATABASE').';User ID='.$quote(required('DB_USERNAME')).';Password='.$quote(required('DB_PASSWORD')).';Encrypt=True;TrustServerCertificate=False;';
    $log = '/tmp/sqlpackage.log';
    $process = proc_open(['sqlpackage', '/Action:Export', '/SourceConnectionString:'.$connection, '/TargetFile:'.$file, '/p:CommandTimeout=300'], [0 => ['file', '/dev/null', 'r'], 1 => ['file', $log, 'w'], 2 => ['file', $log, 'a']], $pipes);
    if (! is_resource($process) || proc_close($process) !== 0 || ! is_file($file) || filesize($file) === 0) {
        throw new RuntimeException('SqlPackage export failed; diagnostic stays local and is not printed');
    }
    $manifest = ['version' => 1, 'created_at' => gmdate(DATE_ATOM), 'image' => required('RELEASE_IMAGE'), 'sqlpackage' => '170.5.96.0', 'database' => ['key' => $prefix.'/database.bacpac', 'bytes' => filesize($file), 'sha256' => hash_file('sha256', $file)], 'media' => []];
    $manifest['includes_media'] = getenv('BACKUP_MEDIA') === 'true';
    $target->putObject(['Bucket' => $bucket, 'Key' => $manifest['database']['key'], 'Body' => fopen($file, 'rb')]);
    $read = $target->getObject(['Bucket' => $bucket, 'Key' => $manifest['database']['key']]);
    $hash = hash_init('sha256');
    while (! $read['Body']->eof()) {
        hash_update($hash, $read['Body']->read(1048576));
    }
    if (hash_final($hash) !== $manifest['database']['sha256']) {
        throw new RuntimeException('Uploaded database checksum mismatch');
    }
    if (getenv('BACKUP_MEDIA') === 'true') {
        $source = new S3Client(['version' => 'latest', 'region' => 'auto', 'endpoint' => required('R2_ENDPOINT'), 'use_path_style_endpoint' => true, 'credentials' => ['key' => required('R2_ACCESS_KEY_ID'), 'secret' => required('R2_SECRET_ACCESS_KEY')]]);
        foreach (['public' => required('R2_PUBLIC_BUCKET'), 'private' => required('R2_PRIVATE_BUCKET')] as $label => $sourceBucket) {
            foreach ($source->getPaginator('ListObjectsV2', ['Bucket' => $sourceBucket]) as $page) {
                foreach ($page['Contents'] ?? [] as $object) {
                    $key = $object['Key'];
                    if (str_starts_with($key, 'livewire-tmp/') || str_starts_with($key, 'deployment-check/')) {
                        continue;
                    }
                    $body = $source->getObject(['Bucket' => $sourceBucket, 'Key' => $key])['Body'];
                    $temp = tmpfile();
                    $hash = hash_init('sha256');
                    while (! $body->eof()) {
                        $chunk = $body->read(1048576);
                        hash_update($hash, $chunk);
                        fwrite($temp, $chunk);
                    }
                    $checksum = hash_final($hash);
                    rewind($temp);
                    $backupKey = $prefix.'/media/'.$label.'/'.$key;
                    $target->putObject(['Bucket' => $bucket, 'Key' => $backupKey, 'Body' => $temp]);
                    fclose($temp);
                    $copied = $target->getObject(['Bucket' => $bucket, 'Key' => $backupKey])['Body'];
                    $hash = hash_init('sha256');
                    while (! $copied->eof()) {
                        hash_update($hash, $copied->read(1048576));
                    }
                    if (hash_final($hash) !== $checksum) {
                        throw new RuntimeException('Media checksum mismatch');
                    }
                    $manifest['media'][] = ['disk' => $label, 'source_key' => $key, 'key' => $backupKey, 'bytes' => (int) $object['Size'], 'sha256' => $checksum];
                }
            }
        }
    }
    // Manifest is the completion marker; partial prefixes must never be restored.
    $target->putObject(['Bucket' => $bucket, 'Key' => $prefix.'/manifest.json', 'Body' => json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT), 'ContentType' => 'application/json']);
    echo json_encode(['status' => 'complete', 'manifest' => $prefix.'/manifest.json', 'database_bytes' => filesize($file), 'media_objects' => count($manifest['media'])], JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    // SDK exceptions can contain signed URLs/credentials; print no exception body.
    fwrite(STDERR, 'Backup failed ('.get_class($exception).'); no completion marker created.'.PHP_EOL);
    exit(1);
} finally {
    @unlink('/tmp/database.bacpac');
    @unlink('/tmp/sqlpackage.log');
}
