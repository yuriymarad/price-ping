<?php

namespace App\Mcp\Tools;

use App\Models\Ticker;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get full details for a specific ticker by symbol, including all its price alert rules.')]
class GetTickerTool extends Tool
{
    public function handle(Request $request): Response
    {
        $symbol = $request->string('symbol') |> trim(...) |> strtoupper(...);

        $ticker = Ticker::with('rules')
            ->bySymbol($symbol)
            ->first();

        if (! $ticker) {
            return Response::text("Ticker '{$symbol}' not found.");
        }

        $data = [
            'symbol' => $ticker->symbol,
            'long_name' => $ticker->long_name,
            'currency' => $ticker->currency,
            'exchange_name' => $ticker->exchange_name,
            'last_price' => $ticker->last_price,
            'last_fetched_at' => $ticker->last_fetched_at?->toIso8601String(),
            'is_hot' => $ticker->is_hot,
            'is_in_portfolio' => $ticker->is_in_portfolio,
            'quantity' => $ticker->quantity,
            'average_price' => $ticker->average_price,
            'notes' => $ticker->notes,
            'alert_rules' => $ticker->rules->map(fn ($rule) => [
                'id' => $rule->id,
                'rule_type' => $rule->rule_type->value,
                'is_active' => $rule->is_active,
                'is_one_shot' => $rule->is_one_shot,
                'threshold_price' => $rule->threshold_price,
                'threshold_direction' => $rule->threshold_direction?->value,
                'percent_value' => $rule->percent_value,
                'period_hours' => $rule->period_hours,
                'percent_direction' => $rule->percent_direction?->value,
                'cooldown_minutes' => $rule->cooldown_minutes,
                'last_alerted_at' => $rule->last_alerted_at?->toIso8601String(),
            ]),
        ];

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'symbol' => $schema->string()->description('The stock ticker symbol, e.g. AAPL')->required(),
        ];
    }
}
