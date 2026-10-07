<?php

namespace Tests\Feature;

use App\Models\Anggota;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberEmailConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_members_may_have_no_email(): void
    {
        foreach (['1001', '1002'] as $nim) {
            Anggota::create(['nim' => $nim, 'nama_lengkap' => 'Tanpa email', 'angkatan' => 2026, 'email' => null]);
        }

        $this->assertSame(2, Anggota::whereNull('email')->count());
    }

    public function test_database_rejects_duplicate_non_null_email(): void
    {
        Anggota::create(['nim' => '1001', 'nama_lengkap' => 'Pertama', 'angkatan' => 2026, 'email' => 'anggota@example.test']);

        $this->expectException(QueryException::class);
        Anggota::create(['nim' => '1002', 'nama_lengkap' => 'Kedua', 'angkatan' => 2026, 'email' => 'anggota@example.test']);
    }
}
