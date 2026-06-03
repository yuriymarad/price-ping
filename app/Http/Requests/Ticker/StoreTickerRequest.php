<?php

namespace App\Http\Requests\Ticker;

use App\Models\Ticker;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTickerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                'string',
                'max:20',
                'alpha_num',
                Rule::unique(Ticker::class, 'symbol'),
            ],
        ];
    }
}
