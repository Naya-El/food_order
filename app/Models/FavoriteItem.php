<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteItem extends Model
{
    use HasFactory;
     public function items()
    {
        return $this->belongsTo(StandardItem::class, 'item_id');

    }
}
