<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\HasImage;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasSlug, HasImage;

    protected $fillable = ['uuid', 'name', 'slug', 'sku', 'brand_id', 'category_id', 'image', 'description', 'model', 'price', 'quantity', 'position', 'status'];

    protected function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'status' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'product_property')->withPivot(['value', 'position']);
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'product_filter');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'product_store')->withPivot(['quantity']);
    }
}
