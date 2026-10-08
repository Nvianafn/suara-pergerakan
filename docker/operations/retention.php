<?php

use Aws\S3\S3Client;

require '/app/vendor/autoload.php';

try {
    $client = new S3Client(['version' => 'latest', 'region' => 'auto', 'endpoint' => getenv('BACKUP_R2_ENDPOINT'), 'use_path_style_endpoint' => true, 'credentials' => ['key' => getenv('BACKUP_R2_ACCESS_KEY_ID'), 'secret' => getenv('BACKUP_R2_SECRET_ACCESS_KEY')]]);
    $bucket = getenv('BACKUP_R2_BUCKET');
    $snapshots = [];
    foreach ($client->getPaginator('ListObjectsV2', ['Bucket' => $bucket, 'Prefix' => 'snapshots/']) as $page) {
        foreach ($page['Contents'] ?? [] as $object) {
            if (! str_ends_with($object['Key'], '/manifest.json')) {
                continue;
            }
            $manifest = json_decode((string) $client->getObject(['Bucket' => $bucket, 'Key' => $object['Key']])['Body'], true, flags: JSON_THROW_ON_ERROR);
            $snapshots[] = ['prefix' => substr($object['Key'], 0, -strlen('manifest.json')), 'manifest' => $manifest];
        }
    }
    usort($snapshots, fn ($a, $b) => strcmp($b['manifest']['created_at'], $a['manifest']['created_at']));
    if (! $snapshots || strtotime($snapshots[0]['manifest']['created_at']) < time() - 86400) {
        throw new RuntimeException('No fresh completed snapshot; retention aborted');
    }
    $keep = [];
    $days = $weeks = $months = [];
    foreach ($snapshots as $snapshot) {
        $timestamp = strtotime($snapshot['manifest']['created_at']);
        $day = gmdate('Y-m-d', $timestamp);
        $week = gmdate('o-W', $timestamp);
        $month = gmdate('Y-m', $timestamp);
        $preserve = false;
        if (! isset($days[$day]) && count($days) < 14) {
            $days[$day] = true;
            $preserve = true;
        }
        // Empty buckets are valid paired media snapshots too.
        if (($snapshot['manifest']['includes_media'] ?? count($snapshot['manifest']['media']) > 0)) {
            if (! isset($weeks[$week]) && count($weeks) < 8) {
                $weeks[$week] = true;
                $preserve = true;
            }
            if (! isset($months[$month]) && count($months) < 12) {
                $months[$month] = true;
                $preserve = true;
            }
        }
        if ($preserve) {
            $keep[$snapshot['prefix']] = true;
        }
    }
    // Verify the newest archive is still readable before deleting old versions.
    foreach ([$snapshots[0]['manifest']['database'], ...$snapshots[0]['manifest']['media']] as $object) {
        $body = $client->getObject(['Bucket' => $bucket, 'Key' => $object['key']])['Body'];
        $hash = hash_init('sha256');
        while (! $body->eof()) {
            hash_update($hash, $body->read(1048576));
        }
        if (hash_final($hash) !== $object['sha256']) {
            throw new RuntimeException('Latest archive validation failed; retention aborted');
        }
    }
    $deleted = 0;
    foreach ($snapshots as $snapshot) {
        if (isset($keep[$snapshot['prefix']])) {
            continue;
        }
        // Remove completion marker first: interrupted pruning cannot look healthy.
        $client->deleteObject(['Bucket' => $bucket, 'Key' => $snapshot['prefix'].'manifest.json']);
        foreach ($client->getPaginator('ListObjectsV2', ['Bucket' => $bucket, 'Prefix' => $snapshot['prefix']]) as $page) {
            foreach ($page['Contents'] ?? [] as $object) {
                $client->deleteObject(['Bucket' => $bucket, 'Key' => $object['Key']]);
            }
        }
        $deleted++;
    }
    echo json_encode(['retention_kept' => count($keep), 'retention_deleted' => $deleted], JSON_THROW_ON_ERROR).PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Retention failed ('.get_class($exception).'); inspect private archives.'.PHP_EOL);
    exit(1);
}
