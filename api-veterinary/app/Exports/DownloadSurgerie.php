<?php

namespace App\Exports;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class DownloadSurgerie implements FromView
{
    protected $surgeries;

    public function __construct($record_surgeries)
    {
        $this->surgeries = $record_surgeries;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view("surgeries.download_excel",
            [
                "surgeries_data" => $this->surgeries,
            ]
        );
    }
}
