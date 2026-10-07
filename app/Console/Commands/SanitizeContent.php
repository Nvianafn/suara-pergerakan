<?php

namespace App\Console\Commands;

use App\Services\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SanitizeContent extends Command
{
    protected $signature = 'content:sanitize';

    protected $description = 'Sanitize legacy rich text without changing archive or publication metadata';

    public function handle(HtmlSanitizer $sanitizer): int
    {
        $count = 0;
        foreach (['karya' => 'konten', 'kegiatan' => 'deskripsi'] as $table => $field) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($records) use ($table, $field, $sanitizer, &$count) {
                foreach ($records as $record) {
                    $clean = $sanitizer->clean($record->{$field});
                    if ($clean !== $record->{$field}) {
                        DB::table($table)->where('id', $record->id)->update([$field => $clean]);
                        $count++;
                    }
                }
            });
        }
        $this->info('Konten dibersihkan: '.$count);

        return self::SUCCESS;
    }
}
