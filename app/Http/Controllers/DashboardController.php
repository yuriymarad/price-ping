<?php

namespace App\Http\Controllers;

use App\Models\Ticker;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('dashboard/Index', [
            'tickers' => Ticker::query()->with(['rules', 'proposals'])->orderedForDisplay()->get(),
        ]);
    }
}
