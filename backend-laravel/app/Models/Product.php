<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'price',
        'category_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function inStock(int $quantity = 1): bool
    {
        return $this->productStock?->quantity >= $quantity;
    }

    public function decrementStock(int $quantity): bool
    {
        $stock = $this->productStock;

        if ($stock && $stock->quantity >= $quantity) {
            $stock->decrement('quantity', $quantity);
            return true;
        }

        return false;
    }

    public function productStock(): HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
}
