<?php

namespace App\Models;

use App\Traits\HasImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory, HasImage;

    protected $fillable = ['product_id', 'image', 'position'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
