<?php

use App\Http\Controllers\AlertRuleController;
use App\Http\Controllers\AlertRuleProposalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TickerController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Route::inertia('/', 'Welcome', [
//     'canRegister' => Features::enabled(Features::registration()),
// ])->name('home');
Route::get('/', fn () => Redirect::to('/login'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('tickers')->name('tickers.')->group(function () {
        Route::post('/', [TickerController::class, 'store'])->name('store');
        Route::post('import', [TickerController::class, 'importPortfolio'])->name('import');
        Route::delete('{ticker}', [TickerController::class, 'destroy'])->name('destroy');
        Route::patch('{ticker}/toggle-hot', [TickerController::class, 'toggleHot'])->name('toggle-hot');
        Route::patch('{ticker}/toggle-portfolio', [TickerController::class, 'togglePortfolio'])->name('toggle-portfolio');
        Route::patch('{ticker}/status', [TickerController::class, 'updateStatus'])->name('update-status');
        Route::patch('{ticker}/portfolio', [TickerController::class, 'updatePortfolio'])->name('update-portfolio');
        Route::get('{ticker}/chart-data', [TickerController::class, 'chartData'])->name('chart-data');
    });

    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::post('rules/bulk', [AlertRuleController::class, 'storeForAll'])->name('rules.bulk');
        Route::post('tickers/{ticker}/rules', [AlertRuleController::class, 'store'])->name('rules.store');
        Route::put('rules/{alertRule}', [AlertRuleController::class, 'update'])->name('rules.update');
        Route::delete('rules/{alertRule}', [AlertRuleController::class, 'destroy'])->name('rules.destroy');
        Route::patch('rules/{alertRule}/toggle', [AlertRuleController::class, 'toggle'])->name('rules.toggle');
        Route::post('tickers/{ticker}/proposals/apply-all', [AlertRuleProposalController::class, 'applyAll'])->name('proposals.apply-all');
        Route::delete('proposals/{alertRuleProposal}', [AlertRuleProposalController::class, 'destroy'])->name('proposals.destroy');
    });
});

require __DIR__.'/settings.php';
