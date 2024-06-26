<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory, HasSlug, HasImage;

    protected $fillable = ['name', 'slug', 'image', 'position', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
