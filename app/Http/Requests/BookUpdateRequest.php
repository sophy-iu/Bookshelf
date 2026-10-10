<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required'],
            'author' => ['required'],
            'isbn' => [
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')
                    ->ignore($this->route('book')),
            ],
            'published_date' => [
                'required',
                'before:today',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image_url' => [
                'nullable',
                'url',
            ],
            'genres' => [
                'required',
                'array',
                'min:1',
            ],
            'genres.*' => [
                'exists:genres,id',
            ],
        ];
    }
}