<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'=>"required|min:3|max:40",
            'email'=>"email|required|unique:users,email",
            'password'=>"required|min:6|max:20|confirmed"
        ];
    }
    public function messages(): array
    {
        return [
            "name.required"=>"name is required",
            "email.required"=>"email is required",
            "password.required"=>"password is required",
            "email.unique"=>"email already exists",
            "password.confirmed"=>"password must be confirmed",

        ];
    }
}
