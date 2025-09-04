<?php

namespace App\Http\Controllers;
use App\Models\CustomizedItem;
use App\Models\ItemIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StandardItem;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{

    public function confirmCart(Request $request)
    {
        $userId = $request->user()->id;
        $currentPoints = User::where('id', $userId)->value('points');
        $points = $request->input('total') / 100;
            User::where('id', $userId)->update([
                'points' => $currentPoints + $points
            ]);


        $order = new Order();
        $order->user_id = $userId;
        $order->status = 'new';
        $order->name = $request->input('name');
        $order->delivery_address = $request->input('delivery_address');
        $order->save();

        if ($request->has('items')) {
            foreach ($request->input('items') as $item) {
                if ($item['item_type'] === 'standard') {
                    // Standard item
                    $standardItem = StandardItem::findOrFail($item['item_id']);

                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->item_id = $standardItem->id;
                    $orderItem->item_type = 'standard';
                    $orderItem->qty = $item['qty'];
                    $orderItem->price = $standardItem->price * $item['qty'];
                    $orderItem->save();

                } elseif ($item['item_type'] === 'customized') {
                    // Customized item
                    $customItem = new CustomizedItem();
                    $customItem->user_id = $userId;
                    $customItem->standard_id = $item['item_id'];
                    $customItem->custom_name = $item['custom_name'] ?? null;
                    $customItem->save();

                    $totalPrice = StandardItem::findOrFail($item['item_id'])->price * $item['qty'];

                    if (!empty($item['ingredients'])) {
                        foreach ($item['ingredients'] as $ingredient) {
                            $itemIngredient = new ItemIngredient();
                            $itemIngredient->item_id = $customItem->id;
                            $itemIngredient->item_type = 'customized';
                            $itemIngredient->ingredient_id = $ingredient['id'];
                            $itemIngredient->qty = $ingredient['qty'];
                            $itemIngredient->is_optional = 0;
                            $itemIngredient->save();

                            if (!empty($ingredient['price'])) {
                                $totalPrice += $ingredient['price'] * $ingredient['qty'];
                            }
                        }
                    }

                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->item_id = $customItem->id;
                    $orderItem->item_type = 'customized';
                    $orderItem->qty = $item['qty'];
                    $orderItem->price = $totalPrice;
                    $orderItem->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ]);
    }





}
