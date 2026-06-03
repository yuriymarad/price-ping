<?php

namespace App\Integrations\MarketData;

use App\Contracts\MarketDataProvider;
use App\Data\ChartPoint;
use App\Data\TickerData;
use App\Enums\ChartRange;
use App\Enums\MarketStatus;
use App\Values\TickerSymbol;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Override;
use Throwable;

class YahooMarketDataProvider implements MarketDataProvider
{
    private const BASE_URL = 'https://query1.finance.yahoo.com';

    #[Override]
    public function getTickerData(TickerSymbol $symbol): ?TickerData
    {
        try {
            $response = $this->yahooHttpClient($symbol);
        } catch (Throwable $e) {
            Log::warning('YahooFinance request failed', ['symbol' => $symbol->value, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $meta = $response->json('chart.result.0.meta');

        if ($meta === null) {
            return null;
        }

        return new TickerData(
            regularMarketPrice: (float) $meta['regularMarketPrice'],
            longName: $meta['longName'] ?? null,
            currency: $meta['currency'] ?? null,
            exchangeName: $meta['exchangeName'] ?? null,
        );
    }

    #[Override]
    public function getMarketStatus(TickerSymbol $symbol): ?MarketStatus
    {
        try {
            $response = $this->yahooHttpClient($symbol);
        } catch (Throwable $e) {
            Log::warning('YahooFinance market status request failed', ['symbol' => $symbol->value, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $meta = $response->json('chart.result.0.meta');

        if ($meta === null) {
            return null;
        }

        $tradingPeriods = $meta['currentTradingPeriod'];
        $now ??= time();

        $within = static fn (?array $period): bool => is_array($period)
            && isset($period['start'], $period['end'])
            && $now >= (int) $period['start']
            && $now < (int) $period['end'];

        return match (true) {
            $within($tradingPeriods['pre'] ?? null) => MarketStatus::PreMarket,
            $within($tradingPeriods['regular'] ?? null) => MarketStatus::Open,
            $within($tradingPeriods['post'] ?? null) => MarketStatus::AfterHours,
            default => MarketStatus::Closed,
        };
    }

    #[Override]
    public function getChartData(TickerSymbol $symbol, ChartRange $range = ChartRange::OneYear): ?array
    {
        try {
            $response = $this->yahooHttpClient($symbol, $range);
        } catch (Throwable $e) {
            Log::warning('YahooFinance chart request failed', ['symbol' => $symbol->value, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $timestamps = $response->json('chart.result.0.timestamp');
        $closes = $response->json('chart.result.0.indicators.quote.0.close');

        if (! is_array($timestamps) || ! is_array($closes)) {
            return null;
        }

        return $this->parseChartPoints($timestamps, $closes);
    }

    private function yahooHttpClient(TickerSymbol $symbol, ChartRange $range = ChartRange::OneMonth): Response
    {
        return Http::baseUrl(self::BASE_URL)
            ->timeout((int) config('services.market_data.timeout', 10))
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'])
            ->retry(2, 500, throw: false)
            ->get("/v8/finance/chart/{$symbol->value}", ['range' => $range->value, 'interval' => $range->interval()]);
    }

    /**
     * @return ChartPoint[]
     */
    private function parseChartPoints(array $timestamps, array $closes): array
    {
        $data = [];
        foreach ($timestamps as $i => $time) {
            $value = $closes[$i] ?? null;
            if ($value !== null && is_numeric($value)) {
                $data[] = new ChartPoint((int) $time, (float) $value);
            }
        }

        return $data;
    }
}
