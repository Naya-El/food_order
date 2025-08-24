<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CustomizedItem;
use App\Models\ItemIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StandardItem;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $userId = $request->user()->id;
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['total_price' => 0]
        );

        $itemType = $request->item_type;
        $itemId = $request->item_id;
        $quantity = $request->quantity;
        $selectedIngredients = $request->input('ingredients', []);

        if ($itemType === 'standard') {
            $cartItem = CartItem::where([
                'cart_id' => $cart->id,
                'item_id' => $itemId,
                'item_type' => 'standard'
            ])->first();

            $standardItem = StandardItem::findOrFail($itemId);

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->price = $cartItem->quantity * $standardItem->price;
                $cartItem->save();
            } else {
                $cartItem = new CartItem();
                $cartItem->cart_id = $cart->id;
                $cartItem->item_id = $itemId;
                $cartItem->item_type = 'standard';
                $cartItem->quantity = $quantity;
                $cartItem->price = $quantity * $standardItem->price;
                $cartItem->save();
            }

        } else if ($itemType === 'customized') {
            $customItem = new CustomizedItem();
            $customItem->user_id = $userId;
            $customItem->standard_id = $itemId;
            $customItem->custom_name = $request->custom_name;
            $customItem->save();

            foreach ($selectedIngredients as $ingredient) {
                $itemIngredient = new ItemIngredient();
                $itemIngredient->item_id = $customItem->id;
                $itemIngredient->item_type = 'customized';
                $itemIngredient->ingredient_id = $ingredient['id'];
                $itemIngredient->qty = $ingredient['qty'];
                $itemIngredient->is_optional = 0;
                $itemIngredient->save();
            }
            $standardItem = StandardItem::findOrFail($itemId);
            $totalPrice = $standardItem->price * $quantity;

            foreach ($selectedIngredients as $ingredient) {
                if (!empty($ingredient['price'])) {
                    $totalPrice += $ingredient['price'] * $ingredient['qty'];
                }
            }
            $cartItem = new CartItem();
            $cartItem->cart_id = $cart->id;
            $cartItem->item_id = $customItem->id;
            $cartItem->item_type = 'customized';
            $cartItem->quantity = $quantity;
            $cartItem->price = $totalPrice;
            $cartItem->save();
        }
        $cart->total_price = $cart->cartItems()->sum('price');
        $cart->save();

        return response()->json(['message' => 'Item added to cart successfully']);
    }

    public function removeItem($itemId)
    {
        $item = CartItem::where('id',$itemId)->first();
        $item->delete();
        return response()->json([
            'success' => true
        ]);
    }

    public function updateItemQuantity(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        CartItem::where('id',$id)->update([
            'quantity'=>$request['quantity']
        ]);

        $item = CartItem::where('id',$id)->first();
        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function clearCart($cartId)
    {
        $cart = Cart::where('id',$cartId)->first();
        if($cart)
        {
            CartItem::where('cart_id',$cartId)->delete();
            Cart::where('id',$cartId)->delete();
            return response()->json([
                'success' => true,
            ]);

        }
    }

    public function confirmCart(Request $request)
    {
        $userId = $request->user()->id;

        $cart = Cart::where('user_id', $userId)->first();
        $currentPoints = User::where('id',$userId)->value('points');

        if($request->coupon_code !== "" && $request->coupon_code !== null)
        {
            $couponData =  Coupon::where('code',$request->coupon_code)->first();
            User::where('id',$userId)->update([
               'points'=> $currentPoints - $couponData['point_qty']
            ]);
        }else{
            $points = $request['total'] / 100;
            User::where('id',$userId)->update([
                'points'=> $currentPoints + $points
            ]);

        }

        $order = new Order();
        $order->user_id = $userId;
        $order->status = 'new';
        $order->name = $request->input('name');
        $order->city = $request->input('city');
        $order->delivery_address = $request->input('delivery_address');
        $order->coupon_id = $couponData->id ?? 0;
        $order->save();

        foreach ($cart->cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->item_id = $cartItem->item_id;
            $orderItem->item_type = $cartItem->item_type;
            $orderItem->qty = $cartItem->quantity;
            $orderItem->price = $cartItem->price;
            $orderItem->save();
        }

        $cart->cartItems()->delete();
        $cart->total_price = 0;
        $cart->save();

        return response()->json([
            'success' => true,
            'order_id' => $order->id
        ]);
    }


}
