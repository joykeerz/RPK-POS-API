<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posProfile()
    {
        return $this->belongsTo(PosProfile::class, 'profile_id');
    }

    public function posAccountancy()
    {
        return $this->hasOne(PosAccountancy::class, 'session_id');
    }
}
