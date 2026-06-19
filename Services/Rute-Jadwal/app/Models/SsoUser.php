<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoUser extends Model
{
    protected $fillable = ['sso_subject', 'roles', 'last_login_at'];

    protected $casts = [
        'roles'         => 'array',
        'last_login_at' => 'datetime',
    ];
}