<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use App\Models\Item;
use App\Models\playerItems;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class PlayersItemController extends Controller
{
    public function addItem(Request $request, $id) {

        //変数宣言
        $count = $request -> count;
        $itemId = $request -> itemId;
        $PlayerSearch = Player::find($id);
        $ItemSearch = Item::find($itemId);

        //➀プレイヤーがアイテムを持っているか問い合わせる処理
        $PlayerItem = playerItems::where('player_id',$PlayerSearch -> id)
            ->where('item_id',$ItemSearch -> id)
            ->first();

        // ➁比較でアイテムが存在すれば処理
        if ($PlayerItem) { 

            // アイテムが既に存在する場合は count を加算
            $PlayerItem = playerItems::where('player_id',$PlayerSearch -> id)
                ->where('item_id',$ItemSearch -> id)
                ->Update(['count' => $PlayerItem->count + $count]);

            // 加算後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId' => $itemId, 'count' => $request -> count]);

        } else { // ➂比較でアイテムが存在しない処理

            // アイテムが存在しない場合は新しく追加
            playerItems::insert([

                'player_id' => $id,
                'item_id' => $itemId,
                'count' => $count
            ]);

            // 追加後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId' => $itemId, 'count' => $count]);
        }
    }
}

