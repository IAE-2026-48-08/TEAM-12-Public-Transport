<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delay extends Model
{
    protected $fillable = [
        'schedule_code',
        'reason',
        'delay_minutes'
    ];
}
