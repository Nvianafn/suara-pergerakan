<?php

use Aws\S3\S3Client;

require '/app/vendor/autoload.php';

// Local, isolated verification only. Never targets the production SQL host.
try {
    $directory = '/restore';
    $s3 = new S3Client(['version' => 'latest', 'region' => 'auto', 'endpoint' => getenv('BACKUP_R2_ENDPOINT'), 'use_path_style_endpoint' => true, 'credentials' => ['key' => getenv('BACKUP_R2_ACCESS_KEY_ID'), 'secret' => getenv('BACKUP_R2_SECRET_ACCESS_KEY')]]);
    $bucket = getenv('BACKUP_R2_BUCKET');
    $manifest = json_decode((string) $s3->getObject(['Bucket' => $bucket, 'Key' => getenv('BACKUP_MANIFEST')])['Body'], true, flags: JSON_THROW_ON_ERROR);
    foreach ([$manifest['database'], ...$manifest['media']] as $index => $object) {
        $path = $directory.'/'.($index === 0 ? 'database.bacpac' : 'media-'.$index);
        $s3->getObject(['Bucket' => $bucket, 'Key' => $object['key'], 'SaveAs' => $path]);
        if (filesize($path) !== (int) $object['bytes'] || hash_file('sha256', $path) !== $object['sha256']) {
            throw new RuntimeException('Downloaded checksum/size mismatch');
        }
    }
    $quote = fn ($value) => '"'.str_replace('"', '""', $value).'"';
    $database = 'suara_restore_'.gmdate('YmdHis');
    $connection = 'Server=suara-pergerakan-sqlserver-1,1433;Initial Catalog='.$database.';User ID=sa;Password='.$quote(getenv('RESTORE_PASSWORD')).';Encrypt=True;TrustServerCertificate=True;';
    $process = proc_open(['sqlpackage', '/Action:Import', '/TargetConnectionString:'.$connection, '/SourceFile:'.$directory.'/database.bacpac'], [0 => ['file', '/dev/null', 'r'], 1 => ['file', $directory.'/import.log', 'w'], 2 => ['file', $directory.'/import.log', 'a']], $pipes);
    if (! is_resource($process) || proc_close($process) !== 0) {
        throw new RuntimeException('Import failed; see private import.log');
    }
    $restored = new PDO('sqlsrv:Server=suara-pergerakan-sqlserver-1,1433;Database='.$database.';Encrypt=yes;TrustServerCertificate=yes', 'sa', getenv('RESTORE_PASSWORD'));
    $source = new PDO('sqlsrv:Server='.getenv('DB_HOST').',1433;Database='.getenv('DB_DATABASE').';Encrypt=yes;TrustServerCertificate=no', getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    $checks = [
        'tables' => 'SELECT COUNT(*) FROM sys.tables WHERE is_ms_shipped=0',
        'filtered_indexes' => 'SELECT COUNT(*) FROM sys.indexes WHERE has_filter=1',
        'foreign_keys' => 'SELECT COUNT(*) FROM sys.foreign_keys',
        'check_constraints' => 'SELECT COUNT(*) FROM sys.check_constraints',
    ];
    foreach ($source->query("SELECT name FROM sys.tables WHERE is_ms_shipped=0 AND name NOT IN ('sessions','cache','cache_locks','jobs','failed_jobs')") as $table) {
        $name = $table['name'];
        $checks['records:'.$name] = 'SELECT COUNT(*) FROM ['.str_replace(']', ']]', $name).']';
    }
    $results = [];
    foreach ($checks as $label => $sql) {
        $a = (int) $source->query($sql)->fetchColumn();
        $b = (int) $restored->query($sql)->fetchColumn();
        if ($a !== $b) {
            throw new RuntimeException('Restore count mismatch: '.$label);
        }
        $results[$label] = $b;
    }
    $results['media_verified'] = count($manifest['media']);
    $results['restored_database'] = $database;
    file_put_contents($directory.'/result.json', json_encode($results, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    echo json_encode($results, JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Restore verification failed ('.get_class($exception).'); diagnostics remain private.'.PHP_EOL);
    exit(1);
}
