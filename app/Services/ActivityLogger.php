<?php
namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log an activity event.
     * @param string $action
     * @param array $meta
     */
    public static function log(string $action, array $meta = [])
    {
        try {
            $user = Auth::user();
            ActivityLog::create([
                'user_id' => $user ? ($user->user_id ?? $user->id) : null,
                'action' => $action,
                'ip_address' => Request::ip(),
                'meta' => $meta ?: null,
            ]);
        } catch (\Throwable $e) {
            // avoid breaking primary flow if logging fails
            \Illuminate\Support\Facades\Log::error('ActivityLogger failed: ' . $e->getMessage());
        }
    }
}
