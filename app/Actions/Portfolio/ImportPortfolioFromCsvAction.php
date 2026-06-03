<?php

namespace App\Actions\Portfolio;

use App\Events\PortfolioImported;
use App\Exceptions\PortfolioImportException;
use App\Models\Ticker;
use App\Values\TickerSymbol;
use Illuminate\Database\Eloquent\Collection;

class ImportPortfolioFromCsvAction
{
    public function handle(string $filePath): Collection
    {
        $csvHandle = fopen($filePath, 'r');

        if ($csvHandle === false) {
            throw PortfolioImportException::unreadableFile();
        }

        $tickers = new Collection;

        try {
            $headers = array_map('trim', fgetcsv($csvHandle));
            $columnMap = array_flip($headers);

            while (($row = fgetcsv($csvHandle)) !== false) {
                $rawSymbol = trim($row[$columnMap['Symbol']] ?? '');
                if ($rawSymbol === '') {
                    continue;
                }

                $ticker = Ticker::updateOrCreate(
                    ['symbol' => new TickerSymbol($rawSymbol)->value],
                    [
                        'long_name' => $row[$columnMap['Description']] ?? null,
                        'is_in_portfolio' => true,
                        'quantity' => isset($columnMap['Quantity']) ? ($row[$columnMap['Quantity']] ?: null) : null,
                        'average_price' => isset($columnMap['OpenPrice']) ? ($row[$columnMap['OpenPrice']] ?: null) : null,
                    ],
                );

                $tickers->push($ticker);
            }
        } finally {
            fclose($csvHandle);
        }

        PortfolioImported::dispatch($tickers->pluck('id')->all());

        return $tickers;
    }
}
