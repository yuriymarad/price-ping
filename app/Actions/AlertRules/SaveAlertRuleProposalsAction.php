<?php

namespace App\Actions\AlertRules;

use App\Data\AlertRuleData;
use App\Http\Requests\AlertRule\StoreAlertRuleRequest;
use App\Models\Ticker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveAlertRuleProposalsAction
{
    /**
     * @param  array<int, array<string, mixed>>  $rules
     *
     * @throws ValidationException
     */
    public function handle(Ticker $ticker, array $rules): int
    {
        $errors = [];
        $validationRules = (new StoreAlertRuleRequest)->rules();

        foreach ($rules as $index => $rule) {
            $validator = Validator::make($rule, $validationRules);

            if ($validator->fails()) {
                $errors["rule[{$index}]"] = $validator->errors()->all();
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($ticker, $rules) {
            $ticker->proposals()->delete();
            $ticker->proposals()->createMany(
                array_map(
                    fn ($rule) => AlertRuleData::fromArray($rule)->toAttributes(),
                    $rules
                )
            );
        });

        return count($rules);
    }
}
