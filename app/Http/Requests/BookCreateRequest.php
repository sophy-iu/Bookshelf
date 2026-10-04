<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookCreateRequest extends FormRequest
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
           'isbn' => ['required', 'digits:13','unique:books,isbn'],
           'published_date' => ['required','before:today'],
           'description' => ['nullable', 'string'],
           'image_url' => ['nullable','url'],
           'genres' => ['required', 'array', 'min:1'],
           'genres.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
           'title.required' => 'タイトルを入力してください',
           'author.required' => '著者名を入力してください',
           'isbn.required' => 'ISBNを入力してください',
           'isbn.digits'    => 'ISBNは半角数字13桁で入力してください',
           'isbn.unique'    => '入力されたISBNが重複しています',
           'published_date.required'    => '出版日を選択してください',
           'published_date.before'    => '有効な日付を選択してください',
           'image_url.url' => 'URL形式で指定してください',
           'genres.required' => 'ジャンルを一つ以上選択してください',
           'genres.min' => 'ジャンルを一つ以上選択してください',
        ];
    }
}