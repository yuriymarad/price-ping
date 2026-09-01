<?php

namespace App\Models;

use App\Data\TickerData;
use App\Enums\TickerStatus;
use Database\Factories\TickerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['symbol', 'long_name', 'currency', 'exchange_name', 'last_price', 'last_fetched_at', 'is_hot', 'is_in_portfolio', 'quantity', 'average_price', 'notes', 'status'])]
class Ticker extends Model
{
    /** @use HasFactory<TickerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_price' => 'decimal:4',
            'last_fetched_at' => 'datetime',
            'is_hot' => 'boolean',
            'is_in_portfolio' => 'boolean',
            'quantity' => 'decimal:4',
            'average_price' => 'decimal:4',
            'status' => TickerStatus::class,
        ];
    }

    /**
     * @return HasMany<AlertRule, $this>
     */
    public function rules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    /**
     * @return HasMany<AlertRuleProposal, $this>
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(AlertRuleProposal::class);
    }

    public function recordMarketPrice(TickerData $tickerData): void
    {
        $this->update([
            'last_price' => $tickerData->regularMarketPrice,
            'last_fetched_at' => now(),
            'currency' => $tickerData->currency ?? $this->currency,
        ]);
    }

    public function markSetupComplete(): void
    {
        $this->update(['status' => TickerStatus::SetupComplete]);
    }

    public function toggleHot(): void
    {
        $this->update(['is_hot' => ! $this->is_hot]);
    }

    public function togglePortfolio(): void
    {
        $this->update(['is_in_portfolio' => ! $this->is_in_portfolio]);
    }

    #[Scope]
    public function bySymbol(Builder $query, string $symbol): void
    {
        $query->where('symbol', $symbol);
    }

    #[Scope]
    public function withPrice(Builder $query): void
    {
        $query->whereNotNull('last_price');
    }

    #[Scope]
    public function orderedForDisplay(Builder $query): void
    {
        $query->orderByDesc('is_hot')
            ->orderByDesc('is_in_portfolio')
            ->orderBy('symbol');
    }
}
