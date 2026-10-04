<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価値を入力してください',
            'rating.integer' => '評価値は整数で入力してください',
            'rating.between' => '評価値は1から5の間で入力してください',
            'comment.required' => 'コメントを入力してください',
        ];
    }
}
