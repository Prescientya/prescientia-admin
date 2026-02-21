<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeviceChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id_old',
        'device_id_new',
        'status',
        'submitted_by',
    ];

    protected static function booted()
    {
        static::saved(function (DeviceChangeRequest $request) {
            $originalStatus = $request->getOriginal('status');
            if ($request->status === 'confirm' && $originalStatus !== 'confirm') {
                $user = $request->user()->first();
                if ($user) {
                    $user->device_id = $request->device_id_new;
                    $user->save();
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
