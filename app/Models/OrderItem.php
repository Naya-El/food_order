<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

        public function item()
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

}
