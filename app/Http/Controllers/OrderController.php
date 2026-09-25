<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CompleteOrderAction;
use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Gate;

class OrderController extends Controller
{
    use ApiResponse;

    //dependency injection
    public function __construct(
        private CreateOrderAction $createOrderAction,
        private CancelOrderAction $cancelOrderAction,
        private CompleteOrderAction $completeOrderAction
    ) {
    }
    public function index(OrderIndexRequest $request){
        $user=auth()->user();
        $query=Order::with('items.product');
            if($user->role!=='admin'){
            $query->where('user_id',$user->id);
        }
        if($request->filled('status')){
            $query->where('status',$request->status);
        }
        if($request->filled('sort')){
            if($request->sort==='oldest'){
                $query->oldest();
            }else{
                $query->latest();
            }
        }

        $orders=$query->paginate($request->per_page ?? 10);
        return OrderResource::collection($orders);
    
    }
    public function show(Order $order){
        Gate::authorize('view',$order);
        $order->load('items.product');
        return new OrderResource($order);
    }
    public function store(StoreOrderRequest $request){
        $data=$request->validated();
        $user=$request->user();
        $order=$this->createOrderAction->execute($data,$user);
        return Self::success('Order Created Successfully',new OrderResource($order),201);
    }


    public function cancel(Order $order){
    Gate::authorize('cancel',$order);
    $order=$this->cancelOrderAction->execute($order);
    return Self::success('order is cancelled',new OrderResource($order));
    }
    public function complete(Order $order){
    Gate::authorize('complete',$order);    
    $order->load('items.products');
    $order=$this->completeOrderAction->execute($order);
        return Self::success('order is completed',new OrderResource($order));
    }
}
