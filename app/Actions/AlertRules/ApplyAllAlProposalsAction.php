<?php

namespace App\Actions\AlertRules;

use App\Data\AlertRuleData;
use App\Enums\TickerStatus;
use App\Models\AlertRuleProposal;
use App\Models\Ticker;
use Illuminate\Support\Facades\DB;

class ApplyAllAlProposalsAction
{
    public function handle(Ticker $ticker): void
    {
        DB::transaction(function () use ($ticker) {
            $ticker->proposals->each(
                fn (AlertRuleProposal $proposal) => $ticker->rules()->create(
                    AlertRuleData::fromProposal($proposal)->toAttributes()
                )
            );
            $ticker->proposals()->delete();
            $ticker->update(['status' => TickerStatus::SetupComplete]);
        });
    }
}
