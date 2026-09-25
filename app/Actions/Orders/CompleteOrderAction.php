<?php

namespace App\Actions\Orders;

use App\Models\Order;

class CompleteOrderAction
{
    public function execute(Order $order){
        $order->update([
            'status'=>"completed"
        ]);
        return $order;
    }
};