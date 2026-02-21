<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'device_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the student profile for the user.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Get the teacher profile for the user.
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Get the admin profile for the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the MBG officer profile for the user.
     */
    public function petugasMbg()
    {
        return $this->hasOne(PetugasMbg::class);
    }

    /**
     * Get all login history for the user.
     */
    public function loginHistory()
    {
        return $this->hasMany(HistoryLogin::class);
    }

    /**
     * Get all wifi presence logs for the user.
     */
    public function wifiPresenceLogs()
    {
        return $this->hasMany(WifiPresenceLog::class);
    }

    /**
     * Get display name for user (admin name or email fallback)
     */
    public function getDisplayNameAttribute()
    {
        if ($this->admin && $this->admin->name) {
            return $this->admin->name;
        }
        return $this->email ?? 'User';
    }
}
