<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DownloadVaccination implements FromView
{
    //__construct sirve para recibir las vacunaciones filtradas desde el controlador 
    protected $vaccinations;
    public function __construct($records_vaccinations)//recibir las vacunaciones filtradas
    {
        $this->vaccinations = $records_vaccinations;
    }
    
    public function view(): View //esta funcion es la que genera la vista para el excel
    {
        return view("vaccinations.download_excel",///la vista que se va a utilizar para generar el excel
            [
                "vaccinations123" => $this->vaccinations,//pasar las vacunaciones filtradas a la vista que genera el excel en la variable vaccinations123
            ]
    );        
    }
 
}
