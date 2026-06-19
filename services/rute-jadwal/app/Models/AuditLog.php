<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['activity_name', 'team_id', 'payload', 'receipt_number'];

    protected $casts = ['payload' => 'array'];
}