<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ApiResponse;
    public function index(){
    return ProductResource::collection(Product::paginate(10));
    }
    public function show(Product $product){

    return new ProductResource($product);
    }

    public function store(StoreProductRequest $request){
    $data=$request->validated();
    if($request->hasFile('image')){
        $data['image']=$request->file('image')->store('products','public');
    }
    $product=Product::create($data);
    return self::success('Product created successfully',new ProductResource($product),201);
    }
    public function update(UpdateProductRequest $request , Product $product){
    
    $data=$request->validated();
    if($request->hasFile('image')){
        if($product->image){
        //undisk the photo from storage
        Storage::disk('public')->delete($product->image);
        }
    $data['image']=$request->file('image')->store('products','public');
    }
    $product->update($data);
        return self::success('Product updated successfully',new ProductResource($product));
    }

    public function destroy(Product $product){
    if($product->image){
    //undisk the photo from storage
    Storage::disk('public')->delete($product->image);
    }
        $product->delete();
        return self::success('Product Deleted Successfully');
    }
}
