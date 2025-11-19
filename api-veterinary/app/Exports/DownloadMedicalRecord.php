<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class DownloadMedicalRecord implements FromView
{
    protected $medical_record;

    public function __construct($medical_record)
    {
        $this->medical_record = $medical_record;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        return view("medical_records.download_excel",
            [
                "medical_records_data" => $this->medical_record,
            ]
        );
    }
}
