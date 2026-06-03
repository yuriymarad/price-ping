<?php

namespace App\Mcp\Tools;

use App\Actions\AlertRules\SaveAlertRuleProposalsAction;
use App\Models\Ticker;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Propose price alert rules for a ticker. Replaces any existing proposals for that ticker. Provide thoughtful rules based on the ticker\'s price history, volatility, and portfolio context.')]
class ProposeAlertRulesTool extends Tool
{
    public function handle(Request $request, SaveAlertRuleProposalsAction $action): Response
    {
        $symbol = $request->string('symbol') |> trim(...) |> strtoupper(...);

        $ticker = Ticker::bySymbol($symbol)->first();

        if (! $ticker) {
            return Response::text("Ticker '{$symbol}' not found.");
        }

        $rules = $request->get('rules', []);

        if (! is_array($rules) || empty($rules)) {
            return Response::text('No rules provided.');
        }

        try {
            $count = $action->handle($ticker, $rules);
        } catch (ValidationException $e) {
            $errorText = collect($e->errors())
                ->map(fn ($msgs, $key) => "{$key}: ".implode(', ', $msgs))
                ->join("\n");

            return Response::text("Validation errors:\n{$errorText}");
        }

        return Response::text("Saved {$count} proposal(s) for {$symbol}.");
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'symbol' => $schema->string()->description('The stock ticker symbol, e.g. AAPL')->required(),
            'rules' => $schema->array()->description('Array of proposed alert rules for this ticker')->items(
                $schema->object([
                    'rule_type' => $schema->string()->description('threshold or percent_change')->required(),
                    'cooldown_minutes' => $schema->integer()->description('Minimum minutes between alerts (1–10080)')->required(),
                    'threshold_price' => $schema->number()->description('Price level to trigger alert (required for threshold rules)'),
                    'threshold_direction' => $schema->string()->description('above or below (required for threshold rules)'),
                    'percent_value' => $schema->number()->description('Percent change to trigger alert (required for percent_change rules)'),
                    'period_hours' => $schema->integer()->description('Lookback window in hours (required for percent_change rules)'),
                    'percent_direction' => $schema->string()->description('up, down, or either (required for percent_change rules)'),
                    'is_one_shot' => $schema->boolean()->description('If true, rule fires once then becomes inactive'),
                ])
            )->required(),
        ];
    }
}
