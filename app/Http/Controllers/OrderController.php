<?php

namespace App\Http\Controllers\CustomerController;

use App\Http\Controllers\Controller;
use App\Models\CustomizedItem;
use App\Models\ItemIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointSetting;
use App\Models\PointsTracking;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function storeOrder(Request $request)
    {

        $user = User::where('id', $request['user_id'])->first();

        $order = new Order();
        $order['user_id'] = $user->id;
        $order['status'] = 'new';
        $order['delivery_address'] = $request['delivery_address'];
        $order['note'] = $request['note'] ?? null;
        $order->save();

        $orderTotal = 0;

        foreach ($request->items as $item) {
            $price = $item['price'];
            $qty   = $item['qty'];
            $orderTotal += $price * $qty;

            if ($item['type'] === 'standard') {
                $orderItem = new OrderItem();
                $orderItem['order_id'] = $order->id;
                $orderItem['item_id'] = $item['id'];
                $orderItem['item_type'] = 'standard';
                $orderItem['qty'] = $qty;
                $orderItem['price'] = $price;
                $orderItem->save();
            }

            elseif ($item['type'] === 'custom') {

                $customized = new  CustomizedItem();
                $customized['user_id'] = $user->id;
                $customized['standard_id'] = $item['standard_id'];
                $customized['custom_name'] = $item['custom_name'];
                $customized->save();

                foreach ($item['ingredients'] as $ingredient) {
                    $ingredientItem = new ItemIngredient();
                    $ingredientItem['item_id'] = $customized->id;
                    $ingredientItem['item_type'] = 'customized';
                    $ingredientItem['ingredient_id'] = $ingredient['id'];
                    $ingredientItem['qty'] = $ingredient['qty'];
                    $ingredientItem->save();
                }

                $orderItem = new OrderItem();
                $orderItem['order_id'] = $order->id;
                $orderItem['item_id'] = $customized->id;
                $orderItem['item_type'] = 'customized';
                $orderItem['qty'] = $qty;
                $orderItem['price'] = $price;
                $orderItem->save();
            }
        }

        $setting = PointSetting::where('point_activity', 'order_total')->first();
        $pointsPerAmount = $setting ? (int) $setting->points_qty : 100;
        $pointsToAdd = floor($orderTotal / $pointsPerAmount);

        if ($pointsToAdd > 0) {
            $user->increment('points', $pointsToAdd);

            PointsTracking::create([
                'user_id'    => $user->id,
                'point_qty'  => $pointsToAdd,
                'description'=> "نقاط عن طلب بقيمة {$orderTotal} ليرة",
            ]);
        }


        return response()->json(['message' => 'تم إنشاء الطلب بنجاح'], 201);
    }

}
