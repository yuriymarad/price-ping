<?php

namespace App\Http\Controllers;

use App\Actions\AlertRules\CreateAlertRuleAction;
use App\Actions\AlertRules\CreateAlertRuleForAllTickersAction;
use App\Actions\AlertRules\UpdateAlertRuleAction;
use App\Data\AlertRuleData;
use App\Http\Requests\AlertRule\StoreAlertRuleRequest;
use App\Http\Requests\AlertRule\UpdateAlertRuleRequest;
use App\Models\AlertRule;
use App\Models\Ticker;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AlertRuleController extends Controller
{
    public function storeForAll(StoreAlertRuleRequest $request, CreateAlertRuleForAllTickersAction $action): RedirectResponse
    {
        $result = $action->handle(AlertRuleData::fromArray($request->validated()));

        $message = "Rule applied to {$result['created']} tickers.";
        if ($result['skipped'] > 0) {
            $message .= " Skipped {$result['skipped']} (duplicate).";
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('dashboard');
    }

    public function store(StoreAlertRuleRequest $request, Ticker $ticker, CreateAlertRuleAction $action): RedirectResponse
    {
        $action->handle($ticker, AlertRuleData::fromArray($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Alert rule created.']);

        return to_route('dashboard');
    }

    public function update(UpdateAlertRuleRequest $request, AlertRule $alertRule, UpdateAlertRuleAction $action): RedirectResponse
    {
        $action->handle($alertRule, AlertRuleData::fromArray($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rule updated.']);

        return to_route('dashboard');
    }

    public function destroy(AlertRule $alertRule): RedirectResponse
    {
        $alertRule->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rule deleted.']);

        return to_route('dashboard');
    }

    public function toggle(AlertRule $alertRule): RedirectResponse
    {
        $alertRule->toggle();

        return to_route('dashboard');
    }
}
