<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosEmployee extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = ['pos_employees'];

}
