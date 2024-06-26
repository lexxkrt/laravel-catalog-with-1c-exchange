<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterGroup extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'name'];

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class);
    }
}
