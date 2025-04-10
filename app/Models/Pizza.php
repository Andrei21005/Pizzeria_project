<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pizza extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'ingredients',
        'proteins',
        'fats',
        'carbohydrates',
    ];

    public function sizes(): HasMany
    {
        return $this->hasMany(PizzaSize::class);
    }
}