<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItems;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PlayersItemController extends Controller
{
    public function addItem(Request $request, $id) {

        try {

            //トランザクション開始
            DB::beginTransaction();

            //変数宣言
            $count = $request->count;
            $itemId = $request->itemId;
            $playerSearch = Player::find($id);
            $itemSearch = Item::find($itemId);

            //➀プレイヤーがアイテムを持っているか問い合わせる処理
            $playerItem = PlayerItems::where('player_id', $playerSearch->id)
                ->where('item_id', $itemSearch->id)
                ->first();

            // ➁比較でアイテムが存在すれば処理
            if ($playerItem) { 

                // アイテムが既に存在する場合は count を加算
                PlayerItems::where('player_id', $playerSearch->id)
                    ->where('item_id', $itemSearch->id)
                    ->Update(['count'=>$playerItem->count + $count]);

                //すべての操作が完了したらコミット
                DB::commit();

                // 加算後のアイテムの数量と itemId をレスポンスとして返す
                return response()->json(['itemId'=>$itemId, 'count'=>$playerItem->count + $count]);

            } else { // ➂比較でアイテムが存在しない処理

                // アイテムが存在しない場合は新しく追加
                PlayerItems::insert([

                    'player_id' => $id,
                    'item_id' => $itemId,
                    'count' => $count
                ]);

                //すべての操作が完了したらコミット
                DB::commit();

                // 追加後のアイテムの数量と itemId をレスポンスとして返す
                return response()->json(['itemId'=>$itemId, 'count'=>$request->count]);
            }

        } catch (\Exception $e) {

            DB::rollback();

            return response('例外が発生しました',400);
        }
    }

    public function useItem(Request $request, $id) {

        try {

            //トランザクション開始
            DB::beginTransaction();

            //変数宣言
            $usecount = 0;
            $count = $request->count;
            $itemId = $request->itemId;

            $playerSearch = Player::where('id', $id)->lockForUpdate()->first();
            $itemSearch = Item::where('id', $itemId)->lockForUpdate()->first();
            
            //プレイヤーがアイテムを持っているか問い合わせる処理
            $playerItem = PlayerItems::where('player_id', $id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            //アイテムが存在する処理
            if($playerItem && $playerItem->count > 0){

                //HP.ver
                if(($itemId == 1)){
            
                    if($playerSearch->hp >= 200) {

                        return response()->json([
                            'itemId'=>$itemSearch->id, 
                            'count'=>$playerItem->count,
                            'player'=>['id'=>$playerSearch->id, 
                                       'hp'=>$playerSearch->hp, 
                                       'mp'=>$playerSearch->mp, 
                                       'message'=>'使っても意味がない。'
                        ]], 200);
                    }

                    for ($i = 0; $i < $count; $i++){

                        if($playerSearch->hp >= 200 || $playerItem->count <= 0){

                            $playerSearch->save();
                            break;
                        }

                        PlayerItems::where('player_id', $playerSearch->id)
                                ->where('item_id', $itemSearch->id)
                                ->Update(['count'=>$playerItem->count -= 1]);
                
                        //回復させる。
                        $playerSearch->hp += $itemSearch->value;
                        $playerSearch->save();
                
                        if($playerSearch->hp >= 200){
                    
                            $playerSearch->hp = 200;
                            $playerSearch->save();
                        }

                        $usecount += 1;
                    } 
                    
                    //すべての操作が完了したらコミット
                    DB::commit();

                    //消費したメッセージ
                    return response()->json([
                        'itemId'=>$itemSearch->id, 
                        'count'=>$playerItem->count,
                        'player'=>['id'=>$playerSearch->id, 
                                   'hp'=>$playerSearch->hp, 
                                   'mp'=>$playerSearch->mp, 
                                   'message'=>'アイテムを'. $usecount. '個消費しました。'
                    ]], 200);
                }

                //MP.ver
                if($itemId == 2){

                    if($playerSearch->mp >= 200) {

                        return response()->json([
                            'itemId'=>$itemSearch->id, 
                            'count'=>$playerItem->count,
                            'player'=>['id'=>$playerSearch->id, 
                                       'hp'=>$playerSearch->hp, 
                                       'mp'=>$playerSearch->mp, 
                                       'message'=>'使っても意味がない。'
                        ]], 200);
                    }

                    for ($i = 0; $i < $count; $i++){

                        if($playerSearch->mp >= 200 || $playerItem->count <= 0){

                            $playerSearch->save();
                            break;
                        }

                        PlayerItems::where('player_id', $playerSearch->id)
                        ->where('item_id', $itemSearch->id)
                        ->Update(['count'=>$playerItem->count -= 1]);
                
                        //回復させる。
                        $playerSearch->mp += $itemSearch->value;
                        $playerSearch->save();
                
                        if($playerSearch->mp >= 200){
                    
                            $playerSearch->mp = 200;
                            $playerSearch->save();
                        }

                        $usecount += 1;
                    } 

                    //すべての操作が完了したらコミット
                    DB::commit();

                    //消費したメッセージ
                    return response()->json([
                        'itemId'=>$itemSearch->id, 
                        'count'=>$playerItem->count,
                        'player'=>['id'=>$playerSearch->id, 
                                   'hp'=>$playerSearch->hp, 
                                   'mp'=>$playerSearch->mp, 
                                   'message'=>'アイテムを'. $usecount. '個消費しました。'
                    ]], 200);
                }

            }else{

                //エラーメッセージ
                return response()->json([
                    'itemId'=>$itemSearch->id, 
                    'count'=>$playerItem->count,
                    'player'=>['id'=>$playerSearch->id, 
                               'hp'=>$playerSearch->hp, 
                               'mp'=>$playerSearch->mp, 
                               'message'=>'アイテムがありません。'
                ]], 400);
            }      

        } catch (\Exception $e) {

            DB::rollback();

            return response('何らかのエラーでアイテムが消費できませんでした。',400);
        }
    }

    public function useGacha(Request $request, $id) {

        try {

            //トランザクション開始
            DB::beginTransaction();
       
            // 変数宣言
            $count = $request->count;
            $playerSearch = Player::find($id);
            
            $itemA = Item::find(1);
            $itemB = Item::find(2);
            
            $getItemA = 0;
            $getItemB = 0;

            $playerMoney =  $playerSearch;
            
            //お金があるかチェック
            if(!$playerMoney || $playerMoney->money < $count * 10){

               return response()->json(['message'=>'お金が足りません'], 400); 
            }

            if($playerMoney){

                // 回すガチャの数分金を引く
                $playerMoney->money -= $count * 10;
                $playerMoney->save();

                // ガチャの結果を初期化
                $results = [];

                // ガチャを引く回数分繰り返す
                for ($i = 0; $i < $count; $i++) {

                    $random = mt_rand(1, 100);
                
                    $item = NULL;

                    // ガチャ確率
                    if ($itemA->percent >= $random) { 

                        $item = $itemA;
                        $getItemA += 1;

                    }else{

                        $random -= $itemA->percent;
                    } 

                    if(($itemB->percent >= $random) && ($item == NULL)){

                        $item = $itemB;
                        $getItemB += 1;
                    }

                    if($item){

                        //➀プレイヤーがアイテムを持っているか問い合わせる処理
                        $playerItem = PlayerItems::where('player_id', $playerSearch->id)
                            ->where('item_id', $item->id)
                            ->first();

                        if ($playerItem) { 

                            // アイテムが既に存在する場合は count を加算
                            PlayerItems::where('player_id', $playerSearch->id)
                                ->where('item_id', $item->id)
                                ->Update(['count'=>$playerItem->count + 1]);

                        } else { 

                            // アイテムが存在しない場合は新しく追加
                            PlayerItems::insert([

                                'player_id' => $id,
                                'item_id' => $item->id,
                                'count' => 1
                            ]);
                        }
                    }
                }

                //itemAの数
                $results[] = [

                    'itemId' => $itemA->id,
                    'count' => $getItemA
                ];

                //itemBの数
                $results[] = [

                    'itemId' => $itemB->id,
                    'count' => $getItemB
                ];

                //全ての操作が成功したらコミット
                DB::commit();

                return response()->json([
                    'results' => $results, 
                    'player'=>[
                        'money'=>$playerMoney->money, 
                            'items'=>PlayerItems::query()
                            ->where('player_id', $playerSearch->id)
                            ->select(['item_id as itemId', 'count'])
                            ->get()
                ]], 200);
            }
                    
                
        } catch (\Exception $e) {/*例外がスローされた場合の処理*/
       
            // 失敗した場合はロールバック
            DB::rollback();
       
            return response('ガチャが回せませんでした。',400);
        }
    }    
}