<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetTickerTool;
use App\Mcp\Tools\ListTickersTool;
use App\Mcp\Tools\ProposeAlertRulesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Ticker Server')]
#[Version('0.0.1')]
#[Instructions('Provides information about stock tickers being tracked in the system, including current prices, portfolio status, and price alert rules.')]
class TickerServer extends Server
{
    protected array $tools = [
        ListTickersTool::class,
        GetTickerTool::class,
        ProposeAlertRulesTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
