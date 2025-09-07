<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardItem extends Model
{
    use HasFactory;

    public function itemIngredients()
    {
        return $this->hasMany(ItemIngredient::class, 'item_id')
            ->with('ingredient');
    }

       public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'item', 'item_type', 'item_id');
    }
}
