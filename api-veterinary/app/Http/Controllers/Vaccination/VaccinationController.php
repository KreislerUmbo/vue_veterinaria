<?php

namespace App\Http\Controllers\Vaccination;

use App\Exports\DownloadVaccination;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vaccination\VaccinationCollection;
use App\Http\Resources\Vaccination\VaccinationResource;
use App\Models\MedicalRecord;
use App\Models\Vaccination\Vaccination;
use App\Models\Vaccination\VaccinationPayment;
use App\Models\Vaccination\VaccinationSchedule;
use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VaccinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   //estos son los filtros que vienen del frontend
        $type_date = $request->type_date;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $state_pay = $request->state_pay;
        $state = $request->state;
        $specie = $request->specie;
        $search_pets = $request->search_pets;
        $search_vets = $request->search_vets;

        // Filtros de busqueda
        $vaccinations = Vaccination::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $vaccinations->lastPage(),// obtener el total de paginas
            "vaccinations" => VaccinationCollection::make($vaccinations),//
        ]);
    }

        public function downloadExcel(Request $request) // esta funcion es para descargar el excel de las vacunaciones
    {
        $type_date = $request->get("type_date");
        $start_date = $request->get("start_date");
        $end_date = $request->get("end_date");
        $state_pay = $request->get("state_pay");
        $state = $request->get("state");
        $specie = $request->get("specie");
        $search_pets = $request->get("search_pets");
        $search_vets = $request->get("search_vets");

        // Filtros de busqueda
        $vaccinations = Vaccination::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->get();

        // Descargar el excel
        return Excel::download(new DownloadVaccination($vaccinations), 'listado_vacunaciones_reporte.xlsx');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        date_default_timezone_set('America/Lima');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->vaccionation_date)->dayName;

        $vaccionation =    Vaccination::create([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            'vaccionation_date' => $request->input('vaccionation_date'),
            'reason' => $request->reason,
            'user_id' => auth('api')->user()->id,
            'amount' => $request->amount,
            'state_pay' => $request->state_pay,
            'outside' => $request->outside,
            'vaccine_names' => $request->vaccine_names,
            'nex_due_date' => $request->nex_due_date,

        ]);

        MedicalRecord::create([
            'pet_id' => $request->pet_id,
            'veterinarie_id' => $request->veterinarie_id,
            'event_type' => 1, // 1=cita medica, 2=vacunacion, 3=cirugia
            'vaccination_id' => $vaccionation->id,
            'event_date' => $request->input('vaccionation_date'),
            'notes' => 'Cita médica creada',
        ]);

        VaccinationPayment::create([
            'vaccination_id' => $vaccionation->id,
            'method_payment' => $request->method_payment,
            'amount' => $request->adelanto,
            'date_payment' => $request->vaccionation_date,
        ]);

        foreach ($request->selected_segment_times as $key => $selected_segment_time) { // recorrer los horarios seleccionados 
            $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
            VaccinationSchedule::create([
                'vaccination_id' => $vaccionation->id,
                'hour' => $schedule_hour->hour,
                'veterinarie_schedule_hour_id' => $selected_segment_time["segment_time_id"], // id del segmento de tiempo
            ]);
        }

        return response()->json(['message' => 'Cita de vacunacion creada exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vaccionation = Vaccination::findOrFail($id);
        return response()->json([
            'vaccionation' => VaccinationResource::make($vaccionation),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        date_default_timezone_set('America/Lima'); // establecer la zona horaria
        Carbon::setLocale('es'); // establecer el idioma a español

        // obtener el nombre del dia de la fecha seleccionada
        $dayName = Carbon::parse($request->vaccionation_date)->dayName;

        $vaccionation = Vaccination::findOrFail($id); // buscar la cita por su id

        if ($vaccionation->state == 3) { // si la cita ya fue atendida no se puede editar
            return response()->json([
                "message" =>  403,
                "message_text" => "No se puede editar esta cita médica porque ya ha sido atendida."
            ]);
        }
            if ($vaccionation->state == 2) { // si la cita ha sido cancelada no se puede editar
            return response()->json([
                "message" =>  403,
                "message_text" => "No se puede editar esta cita médica porque ya ha sido cancelada."
            ]);
        }
        // Validar que el monto no sea menor al adelanto ya pagado
        //$request->amount es el monto del costo de la cita que se quiere actualizar
        // $appointment->payments->sum('amount') es la suma de los adelantos ya pagados que vienen en la relacion payments de la cita
        if ($request->amount < $vaccionation->payments->sum('amount')) { // si el monto es menor al adelanto no se puede editar
            return response()->json([
                "message" => 403,
                "message_text" => "El monto no puede ser menor al adelanto (" . $vaccionation->payments->sum('amount') . "PEN) ya pagado",
            ]);
        }

        // Actualizar los datos de la cita
        $vaccionation->update([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            //'date_appointment' => $request->input('date_appointment'),
            'reason' => $request->reason,
            'amount' => $request->amount,
            'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
            'outside' => $request->outside,
            'vaccine_names' => $request->vaccine_names,
            'nex_due_date' => $request->nex_due_date,
        ]);

        $vaccionation->medical_record->update([ // actualizar el registro medico relacionado a la cita
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
        ]);

        // Actualizar el estado del pago de la cita
        if ($request->amount == $vaccionation->payments->sum('amount')) {
            $vaccionation->update([
                'state_pay' => 3, // 1=pendiente, 2=con adelanto, 3=completo
            ]);
        } else {
            $vaccionation->update([
                'state_pay' => 2, // 1=pendiente, 2=con adelanto, 3=completo
            ]);
        }

        // Actualizar la fecha de la cita solo si se proporciona en la solicitud
        if ($request->vaccionation_date) {
            $vaccionation->update([
                'vaccionation_date' => $request->input('vaccionation_date'),
                'reprogramar' => 1, //si se cambia la fecha de la cita o las horas, se marca como reprogramada
            ]);
            $vaccionation->medical_record->update([ // actualizar el registro medico relacionado a la cita
                'event_date' => $request->input('vaccionation_date'),
                'notes' => 'Cita médica reprogramada',
            ]);
        }

        if (sizeof($request->selected_segment_times) > 0) { //sizeof es para saber el tamaño de un array
            // Eliminar los horarios de la cita existentes
            foreach ($vaccionation->schedules as $key => $schedule) {
                $schedule->delete(); // eliminar los horarios de la cita
            }
            // Agregar los nuevos horarios seleccionados
            foreach ($request->selected_segment_times as $key => $selected_segment_time) {
                $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
                VaccinationSchedule::create([
                    'vaccination_id' => $vaccionation->id,
                    'hour' => $schedule_hour->hour,
                    'veterinarie_schedule_hour_id' => $selected_segment_time["segment_time_id"],
                ]);
            }
        }
        return response()->json(['message' => 'Cita actualizada exitosamente'], 200);
        // otra manera de Eliminar los horarios de la cita existentes
        // AppointmentSchedule::where('appointment_id', $appointment->id)->delete(); //delete es una funcion de laravel para eliminar registros

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vaccionation = Vaccination::findOrFail($id);
        if ($vaccionation->state == 3) {
            return response()->json(['message' =>  403]); // si la cita ya fue atendida no se puede eliminar
        }

        $vaccionation->medical_record->delete(); // eliminar el registro medico relacionado a la cita
        $vaccionation->delete();

        return response()->json(['message' => 'Cita de vacunacion eliminada exitosamente'], 200);
    }
}
