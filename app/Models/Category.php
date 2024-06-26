<?php

namespace App\Models;

use App\Traits\HasImage;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasSlug, HasImage;

    protected $fillable = ['uuid', 'name', 'slug', 'parent_id', 'image', 'position', 'status'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function child(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('child')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(FilterGroup::class, 'category_filter')->withPivot(['position', 'status']);
    }

}
