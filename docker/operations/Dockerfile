ARG APP_IMAGE=ghcr.io/nvianafn/suara-pergerakan@sha256:a3cb3e6c3378740097ba12b9e9ccadb4a6f208db3f12e971d307011efb839f98
FROM ${APP_IMAGE}
USER root
RUN apt-get update && apt-get install -y --no-install-recommends libunwind8 \
    && mkdir -p /opt/sqlpackage \
    && curl -fL --retry 3 'https://go.microsoft.com/fwlink/?linkid=2379816' -o /tmp/sqlpackage.zip \
    && unzip -q /tmp/sqlpackage.zip -d /opt/sqlpackage \
    && chmod +x /opt/sqlpackage/sqlpackage \
    && /opt/sqlpackage/sqlpackage /Version | grep '170.5.96' \
    && rm /tmp/sqlpackage.zip && rm -rf /var/lib/apt/lists/*
ENV PATH="/opt/sqlpackage:${PATH}"
COPY docker/operations/backup.php /ops/backup.php
COPY docker/operations/retention.php /ops/retention.php
ENTRYPOINT ["php", "/ops/backup.php"]
