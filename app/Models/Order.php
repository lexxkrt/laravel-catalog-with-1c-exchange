<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'order_status_id', 'total', 'comment'];

    protected function casts(): array
    {
        return [
            'total' => MoneyCast::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order_status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }
}
