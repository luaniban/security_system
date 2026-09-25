<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Revokes server-side sessions (database driver) after credential changes,
 * so a stolen session cookie stops working once the password is changed.
 */
class SessionManager
{
    public function destroyAll(User $user): void
    {
        DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
    }

    public function destroyOthers(User $user, string $currentSessionId): void
    {
        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}
