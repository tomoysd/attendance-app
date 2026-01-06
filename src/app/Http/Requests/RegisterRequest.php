<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['bail', 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            // 未入力
            'name.required'     => 'お名前を入力してください',
            'email.required'    => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // パスワード規則
            'password.min'      => 'パスワードは8文字以上で入力してください',

            // 確認用パスワード不一致（空でも不一致でもこの文言）
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}
