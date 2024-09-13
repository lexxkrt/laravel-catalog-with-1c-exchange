<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderHistory extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'order_status_id', 'comment'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function order_status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }
}
