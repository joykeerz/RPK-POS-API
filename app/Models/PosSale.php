<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function posOrder()
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function posPayment()
    {
        return $this->belongsTo(PosPayment::class, 'payment_method_id');
    }
}
