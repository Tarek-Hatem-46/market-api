<?php
namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\Product;
use DB;

class CancelOrderAction
{
public function execute(Order $order){
    $order->load('items.products');
    return DB::transaction(function () use ($order) {

            foreach ($order->items as $item) {

                $product = Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                $product->increment('quantity', $item->quantity);
            }

            $order->update([
                'status' => 'cancelled',
            ]);

            return $order;
        });
}
};