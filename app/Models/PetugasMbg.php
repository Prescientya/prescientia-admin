<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetugasMbg extends Model
{
    use SoftDeletes;

    protected $table = 'petugas_mbg';

    protected $fillable = [
        'username',
        'password',
    ];

    protected $dates = ['deleted_at'];
}
