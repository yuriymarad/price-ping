<?php

namespace App\Mcp\Tools;

use App\Models\Ticker;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List all stock tickers currently tracked in the system with their current prices, portfolio status, and alert rule counts.')]
class ListTickersTool extends Tool
{
    public function handle(Request $request): Response
    {
        $tickers = Ticker::withCount('rules')
            ->orderBy('symbol')
            ->get()
            ->map(fn (Ticker $ticker) => [
                'symbol' => $ticker->symbol,
                'long_name' => $ticker->long_name,
                'currency' => $ticker->currency,
                'exchange_name' => $ticker->exchange_name,
                'last_price' => $ticker->last_price,
                'last_fetched_at' => $ticker->last_fetched_at?->toIso8601String(),
                'is_hot' => $ticker->is_hot,
                'is_in_portfolio' => $ticker->is_in_portfolio,
                'quantity' => $ticker->is_in_portfolio ? $ticker->quantity : null,
                'average_price' => $ticker->is_in_portfolio ? $ticker->average_price : null,
                'notes' => $ticker->notes,
                'alert_rules_count' => $ticker->rules_count,
            ]);

        return Response::text(json_encode($tickers, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
