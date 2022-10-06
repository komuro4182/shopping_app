<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Favorite;
use App\Http\Requests\ShopRequest;
use Illuminate\Support\Facades\Auth;


class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shops = Shop::with('user')->latest()->Paginate(4);
        return view('shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('shops.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopRequest $request)
    {
        $shop = new Shop($request->all());
        $shop->user_id = $request->user()->id;

        $file = $request->file('image');
        $shop->image = self::createFileName($file);

        // DB::beginTransaction();
        // try {
        //     $shop->save();
        //     if (!storage::putFileAs('public/images/shops', $file, $shop->image)) {
        //         throw new \Exception('画像ファイルの保存に失敗しました。');
        //     }
        //     DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return back()->withInput()->withErrors($e->getMessage());
        // }


            // トランザクション開始
        DB::beginTransaction();
        try {
            // 登録
            $shop->save();

            // 画像アップロード
            if (!Storage::putFileAs('images/shops', $file, $shop->image)) {
                // 例外を投げてロールバックさせる
                throw new \Exception('画像ファイルの保存に失敗しました。');
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }


        return redirect()
            ->route('shops.show', $shop)
            ->with('notice', '記事を登録しました');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shop = Shop::find($id);
        if (Auth::user()) {
            $favorite = Favorite::where('shop_id', $shop->id)->where('user_id', auth()->user()->id)->first();
            return view('shops.show', compact('shop', 'favorite'));
        } else {
            return view('shops.show', compact('shop'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categories = Category::all();
        $shop = Shop::find($id);
        return view('shops.edit', compact('shop', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function update(ShopRequest $request, $id)
    {
        $shop = Shop::find($id);

        if ($request->user()->cannot('update', $shop)) {
            return redirect()->route('shops.show', $shop)
                ->withErrors('自分の記事以外は更新できません');
        }

        $file = $request->file('image');
        if ($file) {
            $delete_file_path = $shop->image_path;
            $shop->image = self::createFileName($file);
        }
        $shop->fill($request->all());

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 更新
            $shop->save();

            if ($file) {
                // 画像アップロード
                if (!Storage::putFileAs('images/shops', $file, $shop->image)) {
                    // 例外を投げてロールバックさせる
                    throw new \Exception('画像ファイルの保存に失敗しました。');
                }
                // 画像削除
                if (!Storage::delete($delete_file_path)) {
                    //アップロードした画像を削除する
                    Storage::delete($shop->image_path);
                    //例外を投げてロールバックさせる
                    throw new \Exception('画像ファイルの削除に失敗しました。');
                }
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('shops.show', $shop)
            ->with('notice', '記事を更新しました');
    }
    // public function update(ShopRequest $request, $id)
    // {
    //     $shop = Shop::find($id);
    //     $shop->fill($request->all());

    //     if ($request->user()->cannot('update', $shop)) {
    //         return redirect()->route('shops.show', $shop)
    //             ->withErrors('自分の記事以外は更新できません');
    //     }

    //     $file = $request->file('image');
    //     if ($file) {
    //         $delete_file_path = $shop->image_path;
            
    //         $shop->image = self::createFileName($file);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $shop->save();
    //         if ($file) {
    //             if (!Storage::putFileAs('images/shops', $file, $shop->image)) {
    //                 throw new \Exception('画像ファイルの保存に失敗しました。');
    //             }
    //             if (!Storage::delete($delete_file_path)) {
    //                 //アップロードした画像を削除する    // 木村
    //                 Storage::delete($shop->image_path);     // 木村
    //                 throw new \Exception('画像ファイルの削除に失敗しました。');
    //             }
    //         }
    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()->withInput()->withErrors($e->getMessage());
    //     }

    //     return redirect()
    //         ->route('shops.show', $shop)
    //         ->with('notice', '記事を更新しました');
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shop = Shop::find($id);

        DB::beginTransaction();
        try {
            $shop->delete();
            if (!Storage::delete($shop->image_path)) {
                throw new \Exception('画像ファイルの削除に失敗しました。');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('shops.index')
            ->with('notice', '記事を削除しました');
    }
    public static function createFileName($file)
    {
        return date('YmdHis') . '_' . $file->getClientOriginalName();
    }
}

