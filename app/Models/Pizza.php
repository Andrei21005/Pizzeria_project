<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PizzaSize> $sizes
 */
/**
 * @property int $id
 * @property string $name
 * @property string $image_url
 * @property string $ingredients
 * @property string $proteins
 * @property string $fats
 * @property string $carbohydrates
 */
class Pizza extends Model
{
    /**
    * @return HasMany<PizzaSize>
    */
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