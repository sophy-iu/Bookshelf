<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか判定する。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * リクエストに適用されるバリデーションルールを取得する。
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'author' => ['required', 'string'],
            'isbn' => [
                'nullable',
                'digits:13',
                Rule::unique('books', 'isbn')
                    ->ignore($this->route('book')),
            ],
            'published_date' => ['nullable', 'date', 'before:today'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    /**
     * バリデーションエラーメッセージを返す。
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'author.required' => '著者名を入力してください',
            'isbn.digits' => 'ISBNは半角数字13桁で入力してください',
            'isbn.unique' => '入力されたISBNが重複しています',
            'published_date.date' => '有効な日付を選択してください',
            'published_date.before' => '有効な日付を選択してください',
            'image_url.url' => 'URL形式で指定してください',
            'genres.required' => 'ジャンルを一つ以上選択してください',
            'genres.min' => 'ジャンルを一つ以上選択してください',
        ];
    }
}
