<?php

namespace App\Policies;

use App\Models\Biro;
use App\Models\User;
use App\Services\ActivityLog;

class BiroPolicy
{
    public function updateDescription(User $user, Biro $biro): bool
    {
        $allowed = $user->is_active && ($user->role === 'admin_biro' && $user->biro_id === $biro->id);
        if (! $allowed) {
            ActivityLog::record('akses', 'penolakan', $user->id, 'Edit deskripsi biro #'.$biro->id.' ditolak.');
        }

        return $allowed;
    }
}
