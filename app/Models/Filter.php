<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = ['filter_group_id', 'value'];

    public function filter_group(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_filter');
    }
}
