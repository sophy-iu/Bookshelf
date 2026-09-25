<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
    public function rules(): array
    {
        return [
           'title' => ['required'],
           'author' => ['required'],
           'isbn' => ['required', 'digits:13','unique'],
           'url' => ['nullable','url'],
           'date' => ['required','before:today'],
           'image_url' => ['nullable','url'],
           'genre' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
           'title.required' => 'タイトルを入力してください',
           'author.required' => 'タイトルを入力してください',
           'isbn.required' => 'ISBNを入力してください',
           'isbn.digits'    => 'ISBNは半角英数字13桁で入力してください',
           'isbn.unique'    => '入力されたISBNが重複しています',
           'date.required'    => '出版日を選択してください',
           'date.before'    => '有効な日付を選択してください',
           'image_url.url' => 'URL形式で指定してください',
           'genre.required' => 'ジャンルを一つ以上選択してください'
        ];
    }
}