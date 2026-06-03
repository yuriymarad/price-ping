<?php

namespace App\Http\Requests\Ticker;

use App\Enums\ChartRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'range' => ['nullable', Rule::enum(ChartRange::class)],
        ];
    }

    public function chartRange(): ChartRange
    {
        $range = $this->validated('range');

        return $range !== null ? ChartRange::from($range) : ChartRange::OneYear;
    }
}
