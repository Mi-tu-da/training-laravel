<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
        
class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Player::query()->select(['id', 'name', 'hp', 'mp', 'money'])->get(),200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(Player::query()->where('id',$id)->select(['id', 'name', 'hp', 'mp', 'money'])->get(),200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {/*何らかの処理、例外がスローされる可能性がある*/

            //トランザクション開始
            DB::beginTransaction();

            // 新しいプレイヤーレコードをデータベースに挿入し、そのIDを取得する
            $GetId = Player::insertGetId([

                'name'  => $request->name,
                'hp'    => $request->hp,
                'mp'    => $request->mp,
                'money' => $request->money,
            ]);

            //全ての操作が成功したらコミット
            DB::commit();

            // レスポンスにIDを含むJSONを返す
            return response()->json(['id' => $GetId], 200);
        
        } catch (\Exception $e) {/*例外がスローされた場合の処理*/

            // 失敗した場合はロールバック
            DB::rollback();

            return response('追加できませんでした。',400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();
           
            //更新するために問い合わせる
            Player::where('id', $id)->
            update($request -> all());

            DB::commit();

            //logみたいな処理
            return response('更新した。', 200);
        
        } catch (\Exception $e) {

            DB::rollback();

            return response('更新できませんでした。',400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {

            DB::beginTransaction();

            //一致する$idを調べて消す
            Player::where('id', $id)->
            delete();

            DB::commit();

            //logみたいな処理
            return response('削除完了',200);
        
        } catch (\Exception $e) {

            DB::rollback();

            return response('削除できませんでした。',400);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }
}