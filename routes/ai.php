<?php

use App\Mcp\Servers\TickerServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', TickerServer::class);
