<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

        
class PlayersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {/*何らかの処理、例外がスローされる可能性がある*/
           
            //トランザクション開始
            Player::beginTransaction();

            return new Response(

                Player::query()->
                select(['id', 'name','hp','mp','money'])->
                get()
            );
            
            //全ての操作が成功したらコミット
            Player::commit(); 
        
        } catch (\Exception $e) {/*例外がスローされた場合の処理*/
            
            echo '例外が発生しました: ' . $e->getMessage();

            // 失敗した場合はロールバック
            Player::rollback(); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
           
            Player::beginTransaction();

            return new Response(

                Player::query()->
                find($id)
            );
            
            Player::commit();
        
        } catch (\Exception $e) {
            
            echo '例外が発生しました: ' . $e->getMessage();

            Player::rollback();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            Player::beginTransaction();

            // 新しいプレイヤーレコードをデータベースに挿入し、そのIDを取得する
            $GetId = Player::insertGetId([

                'name'  => $request->name,
                'hp'    => $request->hp,
                'mp'    => $request->mp,
                'money' => $request->money,
            ]);

            // レスポンスにIDを含むJSONを返す
            return response()->json(['id' => $GetId], 200);
            
            Player::commit();
        
        } catch (\Exception $e) {
            
            echo '例外が発生しました: ' . $e->getMessage();

            Player::rollback();
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

            Player::beginTransaction();
           
            //更新するために問い合わせる
            Player::where('id', $id)->
            update($request -> all());
       
            //logみたいな処理
            return response('更新した。', 200);
            
            Player::commit();
        
        } catch (\Exception $e) {
            
            echo '例外が発生しました: ' . $e->getMessage();

            Player::rollback();
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

            Player::beginTransaction();

            //一致する$idを調べて消す
            Player::where('id', $id)->
            delete();

            //logみたいな処理
            return response('削除完了',200);
            
            Player::commit();
        
        } catch (\Exception $e) {
            
            echo '例外が発生しました: ' . $e->getMessage();

            Player::rollback();
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