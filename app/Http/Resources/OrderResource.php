<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'total_price'=>$this->total_price,
        'status'=>$this->status,
        'created_at'=>$this->created_at,
        'items'=>ItemResource::collection($this->items),
        'user'=>UserResource::make($this->when(
        $request->user()->role==='admin',
        $this->user))
        ];
    }
}
