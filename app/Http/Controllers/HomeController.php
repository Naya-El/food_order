<?php

namespace App\Http\Controllers;

use App\Models\FavoriteItem;
use App\Models\StandardItem;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function standardItem(Request $request)
    {
        $lang = $request->query('lang', app()->getLocale());

        $items = StandardItem::all()->map(function ($item) use ($lang) {
            $name = json_decode($item->name, true)[$lang] ?? $item->name;

            return [
                'id' => $item->id,
                'name' => $name,
                'type' => $item->type,
                'description' => $item->description,
                'price' => $item->price,
                'image' => asset('storage/' . $item->image),
                'is_available' => (bool) $item->is_available,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $items,
        ]);


    }

    public function itemDetails($itemId)
    {
        $itemData = StandardItem::where('id',$itemId)->first();
        return response()->json([
            'status' => true,
            'itemData' => $itemData,
        ]);
    }

    public function saveFavorite(Request $request)
    {
        if($request->isMethod('post'))
        {
            $user = $request->user();

            $favourite = new FavoriteItem();
            $favourite->user_id = $user->id;
            $favourite->item_id = $request['item_id'];
            $favourite->save();
            return response()->json([
                'success'=>true,
            ]);
        }
    }

    public function favoritesList()
    {
        $user = auth()->user();
        $favourites = FavoriteItem::with('items')->where('user_id',$user->id)->get();
        return response()->json(array(
            'favourites'=>$favourites
        ));
    }

   public function removeFromFavorite($itemId)
    {
        $item = FavoriteItem::where('id',$itemId)->first();
        $item->delete();
        return response()->json(array(
                'success'=>true,
            ));
    }

    public function clearFavorite(Request $request)
    {
        if($request->isMethod('post'))
        {
            FavoriteItem::where('user_id',auth()->user()->id)->delete();
            return response()->json([
                'success'=>true,
            ]);
        }
    }


}
