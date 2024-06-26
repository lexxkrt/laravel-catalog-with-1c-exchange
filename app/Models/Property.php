<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'name', 'property_group_id', 'position'];

    public function property_group(): BelongsTo
    {
        return $this->belongsTo(PropertyGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_property')->withPivot(['value', 'position']);
    }
}
