<?php

namespace App\Policies;

use App\Models\Karya;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ContentPolicy
{
    private function result(User $user, bool $allowed, string $action, ?Model $model = null): bool
    {
        $allowed = $user->is_active && $allowed;
        if (! $allowed) {
            ActivityLog::record('akses', 'penolakan', $user->id, 'Izin '.$action.' ditolak untuk '.($model?->getTable() ?? 'konten').' #'.($model?->id ?? 0));
        }

        return $allowed;
    }

    public function create(User $user): bool
    {
        return $this->result($user, in_array($user->role, ['super_admin', 'admin'], true) || ($user->role === 'admin_biro' && $user->biro_id !== null), 'create');
    }

    public function update(User $user, Model $model): bool
    {
        $allowed = in_array($user->role, ['super_admin', 'admin'], true)
            || ($user->role === 'admin_biro' && $user->biro_id !== null && $model->biro_id === $user->biro_id
                && (! $model instanceof Karya || ($model->status === 'draft' && $model->created_by === $user->id)));

        return $this->result($user, $allowed, 'update', $model);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->result($user, in_array($user->role, ['super_admin', 'admin'], true), 'delete', $model);
    }
}
