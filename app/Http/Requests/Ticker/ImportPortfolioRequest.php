<?php

namespace App\Http\Requests\Ticker;

use Illuminate\Foundation\Http\FormRequest;

class ImportPortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }
}
