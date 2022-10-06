<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function store(Request $request, $id)
    {
        $favorite = new Favorite;
        $shop = Shop::find($id);
        $favorite->shop_id = $shop->id;
        $favorite->user_id = $request->user()->id;
        $favorite->save();
        return back();
    }
    public function destroy(Request $request, $id)
    {
        $favorite = new Favorite;
        $shop = Shop::find($id);
        $user = $request->user()->id;
        $favorite = Favorite::where('shop_id', $shop->id)
            ->where('user_id', $user)
            ->first();
        $favorite->delete();
        return back();
    }
}
