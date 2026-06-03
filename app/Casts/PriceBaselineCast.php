<?php

namespace App\Casts;

use App\Values\PriceBaseline;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<PriceBaseline|null, PriceBaseline|null>
 */
class PriceBaselineCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PriceBaseline
    {
        if ($attributes['baseline_price'] === null || $attributes['baseline_recorded_at'] === null) {
            return null;
        }

        return new PriceBaseline(
            price: (float) $attributes['baseline_price'],
            recordedAt: Carbon::parse($attributes['baseline_recorded_at']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return ['baseline_price' => null, 'baseline_recorded_at' => null];
        }

        return [
            'baseline_price' => $value->price,
            'baseline_recorded_at' => $value->recordedAt,
        ];
    }
}
