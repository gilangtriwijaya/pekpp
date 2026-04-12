<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'action', 'route', 'method', 'path', 'params', 'ip', 'user_agent',
    ];

    protected $casts = [
        'params' => 'array',
    ];

    public static function record($action, $details = [])
    {
        $user = auth()->user();
        return static::create(array_merge([
            'user_id' => $user->id ?? null,
            'action' => (string) $action,
            'route' => optional(request()->route())->getName(),
            'method' => request()->method(),
            'path' => request()->path(),
            'ip' => request()->ip(),
            'user_agent' => (string) request()->header('User-Agent'),
        ], $details));
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
