<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\PlayerItems;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class PlayersItemController extends Controller
{
    public function addItem(Request $request, $id) {

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

            // 加算後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId'=>$itemId, 'count'=>$playerItem->count + $count]);

        } else { // ➂比較でアイテムが存在しない処理

            // アイテムが存在しない場合は新しく追加
            PlayerItems::insert([

                'player_id' => $id,
                'item_id' => $itemId,
                'count' => $count
            ]);

            // 追加後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId'=>$itemId, 'count'=>$request->count]);
        }
    }

    public function useItem(Request $request, $id) {

        //変数宣言
        $count = 1;
        $itemId = $request->itemId;
        $playerSearch = Player::find($id);
        $itemSearch = Item::find($itemId);

        //プレイヤーがアイテムを持っているか問い合わせる処理
        $playerItem = PlayerItems::where('player_id',$playerSearch->id)
            ->where('item_id',$itemSearch->id)
            ->first();

        //アイテムが存在する処理
        if($playerItem && $playerItem->count > 0){

            //HP.ver
            if(($itemId == 1)){
            
                if($playerSearch->hp >= 200) {

                    return response()->json(['itemId'=>$itemSearch->id, 'count'=>$playerItem->count,'player'=>['id'=>$playerSearch->id, 
                        'hp'=>$playerSearch->hp, 'mp'=>$playerSearch->mp, 'message'=>'使っても意味がない。']], 200);
                }

                PlayerItems::where('player_id', $playerSearch->id)
                ->where('item_id', $itemSearch->id)
                ->Update(['count'=>$playerItem->count - $count]);
                
                //回復させる。
                $playerSearch->hp += $itemSearch->value;
                $playerSearch->save();
                
                if($playerSearch->hp >= 200){
                    
                    $playerSearch->hp = 200;
                    $playerSearch->save();
                }

                //消費したメッセージ
                return response()->json(['itemId'=>$itemSearch->id, 'count'=>$playerItem->count,'player'=>['id'=>$playerSearch->id, 
                    'hp'=>$playerSearch->hp, 'mp'=>$playerSearch->mp, 'message'=>'アイテムを消費しました。']], 200);
            }

            //MP.ver
            if($itemId == 2){

                if($playerSearch->mp >= 200) {
                    
                    return response()->json(['itemId'=>$itemSearch->id, 'count'=>$playerItem->count,'player'=>['id'=>$playerSearch->id, 
                        'hp'=>$playerSearch->hp, 'mp'=>$playerSearch->mp, 'message'=>'使っても意味がない。']], 200);
                }

                PlayerItems::where('player_id', $playerSearch->id)
                    ->where('item_id', $itemSearch->id)
                    ->Update(['count'=>$playerItem->count - $count]);
                
                $playerSearch->mp += $itemSearch->value;
                $playerSearch->save();
            
                if($playerSearch->mp >= 200){
                    
                    $playerSearch->mp = 200;
                    $playerSearch->save();
                }

                //消費したメッセージ
                return response()->json(['itemId'=>$itemSearch->id, 'count'=>$playerItem->count,'player'=>['id'=>$playerSearch->id, 
                    'hp'=>$playerSearch->hp, 'mp'=>$playerSearch->mp, 'message'=>'アイテムを消費しました。']], 200);
            }

        }else{

            //エラーメッセージ
            return response()->json(['itemId'=>$itemSearch->id, 'count'=>$playerItem->count,'player'=>['id'=>$playerSearch->id, 
                'hp'=>$playerSearch->hp, 'mp'=>$playerSearch->mp, 'message'=>'アイテムがありません。']], 400);
        }      
    }

    public function useGacha(Request $request, $id) {

        // 変数宣言
        $count = $request->count;
        $playerSearch = Player::find($id);
        
        $itemA = Item::find(1);
        $itemB = Item::find(2);
        
        $getItemA = 0;
        $getItemB = 0;

        // プレイヤーが金を持っているか問い合わせる処理
        $playerMoney = Player::where('id', $playerSearch->id) 
            ->where('money', '>=', $count * 10) 
            ->first();
        
        if(!$playerMoney || $playerMoney->money < $count * 10){

           //エラーメッセージ
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
                
                if($itemB->percent >= $random){
                    
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

            return response()->json([
                'results' => $results, 
                'player'=>[
                    'money'=>$playerSearch->money, 
                        'items'=>PlayerItems::query()
                        ->where('player_id', $playerSearch->id)
                        ->select(['item_id as itemId', 'count'])
                        ->get()]], 200);
        }   
    }    
}

