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

        $items = StandardItem::with('itemIngredients.ingredient')
            ->where('is_available', 1)
            ->get()
            ->map(function ($item) use ($lang) {
                $decoded = json_decode($item->name, true);
                $name = is_array($decoded) ? ($decoded[$lang] ?? $item->name) : $item->name;

                return [
                    'id' => $item->id,
                    'name' => $name,
                    'category_id' => $item->category_id,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'is_available' => (bool) $item->is_available,
                    'new' => (bool) $item->new,
                    'popular' => (bool) $item->popular,
                    'ingredients' => $item->itemIngredients->map(function ($ii) use ($lang) {
                        $decoded = json_decode($ii->ingredient->name, true);
                        $ingredientName = is_array($decoded) ? ($decoded[$lang] ?? $ii->ingredient->name) : $ii->ingredient->name;

                        return [
                            'id' => $ii->ingredient->id,
                            'name' => $ingredientName,
                        ];
                    }),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $items,
        ]);
    }


    public function categories(Request $request)
    {
        $lang = $request->query('lang', app()->getLocale());

        $categories = Category::where('is_available', 1)->get()->map(function ($cat) use ($lang) {
            $decoded = json_decode($cat->name, true);
            $name = is_array($decoded) ? ($decoded[$lang] ?? $cat->name) : $cat->name;

            return [
                'id' => $cat->id,
                'name' => $name,
                'is_available' => (bool) $cat->is_available,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $categories,
        ]);
    }


    public function itemFilter(Request $request)
    {
        $lang = $request->query('lang', app()->getLocale());
        $categoryId = $request['category_id'];

        $items = StandardItem::with('itemIngredients.ingredient')
            ->where('is_available', 1)
            ->where(function ($query) {
                $query->where('new', 1)
                    ->orWhere('popular', 1);
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->get()
            ->map(function ($item) use ($lang) {
                $decoded = json_decode($item->name, true);
                $name = is_array($decoded) ? ($decoded[$lang] ?? $item->name) : $item->name;

                return [
                    'id' => $item->id,
                    'name' => $name,
                    'category_id' => $item->category_id,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'is_available' => (bool) $item->is_available,
                    'new' => (bool) $item->new,
                    'popular' => (bool) $item->popular,
                    'ingredients' => $item->itemIngredients->map(function ($ii) use ($lang) {
                        $decoded = json_decode($ii->ingredient->name, true);
                        $ingredientName = is_array($decoded) ? ($decoded[$lang] ?? $ii->ingredient->name) : $ii->ingredient->name;

                        return [
                            'id' => $ii->ingredient->id,
                            'name' => $ingredientName,
                        ];
                    }),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $items,
        ]);
    }

    public function itemDetails($itemId)
    {
        $itemData = StandardItem::where('id', $itemId)->first();
        return response()->json([
            'status' => true,
            'itemData' => $itemData,
        ]);
    }

    public function saveFavorite(Request $request)
    {
        if ($request->isMethod('post')) {
            $user = $request->user();

            $favourite = new FavoriteItem();
            $favourite->user_id = $user->id;
            $favourite->item_id = $request['item_id'];
            $favourite->save();

            return response()->json([
                'success' => true,
            ]);
        }
    }

    public function favoritesList(Request $request)
    {
        $user = $request->user();
        $lang = $request->query('lang', app()->getLocale());

        $favourites = FavoriteItem::with('items.itemIngredients.ingredient')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($fav) use ($lang) {
                $item = $fav->items;

                if (!$item) return null;

                $decoded = json_decode($item->name, true);
                $name = is_array($decoded) ? ($decoded[$lang] ?? $item->name) : $item->name;

                return [
                    'id' => $item->id,
                    'name' => $name,
                    'type' => $item->type,
                    'description' => $item->description,
                    'price' => $item->price,
                    'image' => $item->image ? asset('storage/app/public/' . $item->image) : null,
                    'is_available' => (bool) $item->is_available,
                    'ingredients' => $item->itemIngredients->map(function ($ii) use ($lang) {
                        $decoded = json_decode($ii->ingredient->name, true);
                        $ingredientName = is_array($decoded) ? ($decoded[$lang] ?? $ii->ingredient->name) : $ii->ingredient->name;

                        return [
                            'id' => $ii->ingredient->id,
                            'name' => $ingredientName,
                            'unit' => $ii->ingredient->unit,
                            'price' => $ii->ingredient->price,
                            'image' => $ii->ingredient->image ? asset('storage/app/public/' . trim($ii->ingredient->image)) : null,
                        ];
                    }),
                ];
            })
            ->filter(); // Remove nulls in case any favorite has no item

        return response()->json([
            'status' => true,
            'data' => $favourites,
        ]);
    }

    public function removeFromFavorite($itemId)
    {
        $item = FavoriteItem::where('id', $itemId)->first();
        $item->delete();
        return response()->json([
            'success' => true,
        ]);
    }

    public function clearFavorite()
    {
        FavoriteItem::where('user_id', auth()->user()->id)->delete();
        return response()->json([
            'success' => true,
        ]);
    }
}
