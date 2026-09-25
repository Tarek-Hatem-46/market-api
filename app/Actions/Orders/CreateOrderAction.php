<?php
namespace App\Actions\Orders;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use DB;

class CreateOrderAction
{

public function execute(array $data ,User $user){
    
    return DB::transaction(function() use ($data,$user){

        $order=Order::create([
            'status'=>'pending',
            'total_price'=>0,
            'user_id'=>$user->id,
        ]);

        $total=0;
        
        foreach($data['items'] as $item){
            $product=Product::where('id',$item["product_id"])->lockForUpdate()->first();

            if($item['quantity'] > $product->quantity){
                throw new InsufficientStockException("Insufficient stock for {$product->name}");
            }

            $price=$product->price;
            $quantity=$item['quantity'];

            $order->items()->create([
                'product_id'=>$product->id,
                'quantity'=>$quantity,
                'price'=>$price
            ]);
            $total+=$quantity * $price;

            $product->decrement('quantity',$quantity);
            }
            $order->update([
                'total_price'=>$total
            ]);
            return $order;
        });
}
}