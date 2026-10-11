<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'author' => ['required', 'string'],
            'isbn' => [
                'nullable',
                'string',
                'size:13',
                Rule::unique('books', 'isbn')
                    ->ignore($this->route('book')),
            ],
            'published_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'author.required' => '著者名を入力してください。',

            'isbn.size' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',

            'published_date.date' => '出版日は正しい日付形式で入力してください。',

            'image_url.url' => '画像URLは正しいURL形式で入力してください。',

            'genres.required' => 'ジャンルを選択してください。',
            'genres.array' => 'ジャンルは配列で指定してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください。',
            'genres.*.exists' => '指定されたジャンルが存在しません。',
        ];
    }
}
