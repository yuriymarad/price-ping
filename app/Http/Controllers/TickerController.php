<?php

namespace App\Http\Controllers;

use App\Actions\Portfolio\ImportPortfolioFromCsvAction;
use App\Actions\Tickers\CreateTickerAction;
use App\Actions\Tickers\FetchChartDataAction;
use App\Exceptions\PortfolioImportException;
use App\Http\Requests\Ticker\ChartDataRequest;
use App\Http\Requests\Ticker\ImportPortfolioRequest;
use App\Http\Requests\Ticker\StoreTickerRequest;
use App\Http\Requests\Ticker\UpdateTickerPortfolioRequest;
use App\Http\Requests\Ticker\UpdateTickerStatusRequest;
use App\Models\Ticker;
use App\Values\TickerSymbol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TickerController extends Controller
{
    public function store(StoreTickerRequest $request, CreateTickerAction $action): RedirectResponse
    {
        $ticker = $action->handle(new TickerSymbol($request->validated('symbol')));

        if ($ticker === null) {
            return back()->withErrors(['symbol' => 'Symbol not found on Yahoo Finance.']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$ticker->symbol} added."]);

        return to_route('dashboard');
    }

    public function destroy(Ticker $ticker): RedirectResponse
    {
        $symbol = $ticker->symbol;
        $ticker->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$symbol} removed."]);

        return to_route('dashboard');
    }

    public function toggleHot(Ticker $ticker): RedirectResponse
    {
        $ticker->toggleHot();

        return to_route('dashboard');
    }

    public function togglePortfolio(Ticker $ticker): RedirectResponse
    {
        $ticker->togglePortfolio();

        return to_route('dashboard');
    }

    public function updatePortfolio(UpdateTickerPortfolioRequest $request, Ticker $ticker): RedirectResponse
    {
        $ticker->update($request->validated());

        return to_route('dashboard');
    }

    public function importPortfolio(ImportPortfolioRequest $request, ImportPortfolioFromCsvAction $action): RedirectResponse
    {
        try {
            $tickers = $action->handle($request->file('file')->getRealPath());
        } catch (PortfolioImportException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "Imported {$tickers->count()} tickers from portfolio."]);

        return to_route('dashboard');
    }

    public function updateStatus(UpdateTickerStatusRequest $request, Ticker $ticker): RedirectResponse
    {
        $ticker->update($request->validated());

        return to_route('dashboard');
    }

    public function chartData(Ticker $ticker, ChartDataRequest $request, FetchChartDataAction $action): JsonResponse
    {
        $data = $action->handle(new TickerSymbol($ticker->symbol), $request->chartRange());

        if ($data === null) {
            return response()->json(['error' => 'Failed to fetch chart data'], 422);
        }

        return response()->json($data);
    }
}
