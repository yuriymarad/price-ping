<?php

namespace App\Http\Controllers;

use App\Actions\AlertRules\ApplyAllAlProposalsAction;
use App\Models\AlertRuleProposal;
use App\Models\Ticker;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AlertRuleProposalController extends Controller
{
    public function applyAll(Ticker $ticker, ApplyAllAlProposalsAction $action): RedirectResponse
    {
        $action->handle($ticker);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'AI-proposed rules applied.']);

        return to_route('dashboard');
    }

    public function destroy(AlertRuleProposal $alertRuleProposal): RedirectResponse
    {
        $alertRuleProposal->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proposal discarded.']);

        return to_route('dashboard');
    }
}
