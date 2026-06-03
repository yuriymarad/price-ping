<?php

namespace App\Http\Requests\AlertRule;

use App\Enums\AlertRuleType;
use App\Enums\PercentDirection;
use App\Enums\ThresholdDirection;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlertRuleRequest extends FormRequest
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
            'rule_type' => ['required', Rule::enum(AlertRuleType::class)],
            'cooldown_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'threshold_price' => ['nullable', 'required_if:rule_type,threshold', 'numeric', 'min:0'],
            'threshold_direction' => ['nullable', 'required_if:rule_type,threshold', Rule::enum(ThresholdDirection::class)],
            'percent_value' => ['nullable', 'required_if:rule_type,percent_change', 'numeric', 'min:0.01', 'max:1000'],
            'period_hours' => ['nullable', 'required_if:rule_type,percent_change', 'integer', 'min:1', 'max:720'],
            'percent_direction' => ['nullable', 'required_if:rule_type,percent_change', Rule::enum(PercentDirection::class)],
            'is_one_shot' => ['nullable', 'boolean'],
        ];
    }
}
