<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;

class CategoryController extends Controller
{
    use ApiResponse;
    public function index(){
        return CategoryResource::collection(Category::paginate(10));
    }
    public function show(Category $category){
        return new CategoryResource($category);
    }
    public function store(StoreCategoryRequest $request){
        $data =$request->validated();
        $category=Category::create($data);
        return self::success("Category Created Successfully",new CategoryResource($category),201);
    }

    public function update(UpdateCategoryRequest $request , Category $category){
        $data=$request->validated();
        $category->update($data);
        return self::success("Category Updated Successfully",new CategoryResource($category));
    }
    public function destroy(Category $category){
        $category->delete();
        return self::success("Category Deleted Successfully");
    }
}
