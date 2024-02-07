<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosAccountancy extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProfile()
    {
        return $this->belongsTo(PosProfile::class, 'profile_id');
    }

    public function posSession()
    {
        return $this->belongsTo(PosSession::class, 'session_id');
    }
}
