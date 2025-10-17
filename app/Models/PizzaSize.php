<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PizzaSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'pizza_id',
        'size_name',
        'diameter',
        'weight',
        'price',
    ];
    
    /**
    * @return BelongsTo<Pizza, PizzaSize>
    */
    public function pizza(): BelongsTo
    {
        return $this->belongsTo(Pizza::class);
    }
}
