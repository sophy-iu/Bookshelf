<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
            ],
            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '書籍IDは整数で指定してください。',
            'book_id.exists' => '指定された書籍が存在しません。',

            'target_date.required' => '目標日を入力してください。',
            'target_date.date' => '目標日は正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '目標日は今日以降の日付を指定してください。',
        ];
    }
}