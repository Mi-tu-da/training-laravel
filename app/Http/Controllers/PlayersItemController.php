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
        $count = $request -> count;
        $itemId = $request -> itemId;
        $playerSearch = Player::find($id);
        $itemSearch = Item::find($itemId);

        //➀プレイヤーがアイテムを持っているか問い合わせる処理
        $playerItem = PlayerItems::where('player_id',$playerSearch -> id)
            ->where('item_id',$itemSearch -> id)
            ->first();

        // ➁比較でアイテムが存在すれば処理
        if ($playerItem) { 

            // アイテムが既に存在する場合は count を加算
            PlayerItems::where('player_id',$playerSearch -> id)
                ->where('item_id',$itemSearch -> id)
                ->Update(['count' => $playerItem->count + $count]);

            // 加算後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId' => $itemId, 'count' => $playerItem->count + $count]);

        } else { // ➂比較でアイテムが存在しない処理

            // アイテムが存在しない場合は新しく追加
            PlayerItems::insert([

                'player_id' => $id,
                'item_id' => $itemId,
                'count' => $count
            ]);

            // 追加後のアイテムの数量と itemId をレスポンスとして返す
            return response()->json(['itemId' => $itemId, 'count' => $request -> count]);
        }

    }
}

