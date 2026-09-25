<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'name'=>"required|min:2|max:55",
        'description'=>"min:2|max:200",
        'price'=>"required|numeric",
        'quantity'=>"required|integer|min:0",
        'image'=>"mimes:jpg,jpeg,png,avif,svg",
        'category_id'=>'required|integer|exists:categories,id',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required'=>'name is required',
            'name.min'=>'name is too short must be more than 1 letter ',
            'name.max'=>'name is too long must be less than 56 letter ',
            'description.min'=>'description is too short must be more than 1 letter ',
            'description.max'=>'description is too long must be less than 56 letter ',
            'image.mimes'=>'image must be between(jpg,jpeg,png,avif,svg)'
        ];
    }

}
