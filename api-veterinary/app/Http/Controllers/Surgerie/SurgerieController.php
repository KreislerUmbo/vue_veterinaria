<?php

namespace App\Http\Controllers\Surgerie;

use App\Exports\DownloadSurgerie;
use App\Http\Controllers\Controller;
use App\Http\Resources\Surgerie\SurgerieCollection;
use App\Http\Resources\Surgerie\SurgerieResource;
use App\Models\MedicalRecord;
use App\Models\Surgerie\Surgerie;
use App\Models\Surgerie\SurgeriePayment;
use App\Models\Surgerie\SurgerieSchedule;
use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SurgerieController extends Controller
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
        $surgeries = Surgerie::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $surgeries->lastPage(), // obtener el total de paginas
            "surgeries" => SurgerieCollection::make($surgeries), //
        ]);
    }

    public function downloadExcel(Request $request) // esta funcion es para descargar el excel de las cirugias
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
        $surgeries = Surgerie::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->get();

        // Descargar el excel
        return Excel::download(new DownloadSurgerie($surgeries), 'listado_cirugias_reporte.xlsx');
    }

    public function store(Request $request)
    {
        date_default_timezone_set('America/Lima');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->surgerie_date)->dayName;

        $surgerie =    Surgerie::create([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            'surgerie_date' => $request->input('surgerie_date'),
            'medical_notes' => $request->medical_notes,
            'user_id' => auth('api')->user()->id,
            'amount' => $request->amount,
            'state_pay' => $request->state_pay,
            'outside' => $request->outside,
            'outcome' => $request->outcome, // resultado de la cirugia
            'surgerie_type' => $request->surgerie_type,

        ]);

        MedicalRecord::create([
            'pet_id' => $request->pet_id,
            'veterinarie_id' => $request->veterinarie_id,
            'event_type' => 3, // 1=cita medica, 2=vacunacion, 3=cirugia
            'surgerie_id' => $surgerie->id,
            'event_date' => $request->input('surgerie_date'),
            'notes' => $request->outcome,// resultado de la cirugia se coloca en las notas del registro medico
        ]);

        SurgeriePayment::create([
            'surgerie_id' => $surgerie->id,
            'method_payment' => $request->method_payment,
            'amount' => $request->adelanto,
            'date_payment' => $request->surgerie_date,
        ]);

        foreach ($request->selected_segment_times as $key => $selected_segment_time) { // recorrer los horarios seleccionados 
            $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
            SurgerieSchedule::create([
                'surgerie_id' => $surgerie->id,
                'hour' => $schedule_hour->hour,
                'veterinarie_shedule_hour_id' => $selected_segment_time["segment_time_id"], // id del segmento de tiempo
            ]);
        }

        return response()->json(['message' => 'Elprocedimiento quirurgico se ha creada exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $surgerie = Surgerie::findOrFail($id);
        return response()->json([
            "surgerie" => SurgerieResource::make($surgerie),
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
        $dayName = Carbon::parse($request->surgerie_date)->dayName;

        $surgerie = Surgerie::findOrFail($id); // buscar la cita por su id

        if ($surgerie->state == 3) { // si la cirugia ya fue atendida no se puede editar
            return response()->json([
                "message" =>  403,
                "message_text" => "No se puede editar esta cirugia porque ya ha sido atendida."
            ]);
        }
        // Validar que el monto no sea menor al adelanto ya pagado
        //$request->amount es el monto del costo de la cita que se quiere actualizar
        // $appointment->payments->sum('amount') es la suma de los adelantos ya pagados que vienen en la relacion payments de la cita
        if ($request->amount < $surgerie->payments->sum('amount')) { // si el monto es menor al adelanto no se puede editar
            return response()->json([
                "message" => 403,
                "message_text" => "El monto no puede ser menor al adelanto (" . $surgerie->payments->sum('amount') . "PEN) ya pagado",
            ]);
        }

        // Actualizar los datos de la cita
        $surgerie->update([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            'medical_notes' => $request->medical_notes,
            'amount' => $request->amount,
            'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
            'outside' => $request->outside,
            'outcome' => $request->outcome, // resultado de la cirugia
            'surgerie_type' => $request->surgerie_type,
        ]);

        $surgerie->medical_record->update([ // actualizar el registro medico relacionado a la cirugia
            // 'event_date' => $request->input('surgerie_date'),
            // 'notes' => 'procedimiento quirurgico actualizada',
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'notes' => $request->outcome, // resultado de la cirugia se coloca en las notas del registro medico
        ]);

        // Actualizar el estado del pago de la cita
        if ($request->amount == $surgerie->payments->sum('amount')) {
            $surgerie->update([
                'state_pay' => 3, // 3=completo
            ]);
        } else {
            $surgerie->update([
                'state_pay' => 2, // 2=con adelanto
            ]);
        }

        // Actualizar la fecha de la cita solo si se proporciona en la solicitud
        if ($request->surgerie_date) {
            $surgerie->update([
                'surgerie_date' => $request->input('surgerie_date'),
                'reprogramar' => 1, //si se cambia la fecha de la cita o las horas, se marca como reprogramada
            ]);
            $surgerie->medical_record->update([ // actualizar el registro medico relacionado a la cita
                'event_date' => $request->input('date_appointment'),
                'notes' => 'Procedimiento qirurgico reprogramada',
            ]);
        }

        if (sizeof($request->selected_segment_times) > 0) { //sizeof es para saber el tamaño de un array
            // Eliminar los horarios de la cita existentes
            foreach ($surgerie->schedules as $key => $schedule) {
                $schedule->delete(); // eliminar los horarios de la cita
            }
            // Agregar los nuevos horarios seleccionados
            foreach ($request->selected_segment_times as $key => $selected_segment_time) {
                $schedule_hour = VeterinarieScheduleHour::find($selected_segment_time["segment_time_id"]);
                SurgerieSchedule::create([
                    'surgerie_id' => $surgerie->id,
                    'hour' => $schedule_hour->hour,
                    'veterinarie_shedule_hour_id' => $selected_segment_time["segment_time_id"],
                ]);
            }
        }
        return response()->json(['message' => 'Procedimiento quirurgico actualizada exitosamente'], 200);
        // otra manera de Eliminar los horarios de la cita existentes
        // AppointmentSchedule::where('appointment_id', $appointment->id)->delete(); //delete es una funcion de laravel para eliminar registros

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $surgerie = Surgerie::findOrFail($id);
        if ($surgerie->state == 3) {
            return response()->json(['message' =>  403]); // si cirugia  ya fue atendida no se puede eliminar
        }

        $surgerie->medical_record->delete(); // eliminar el registro medico relacionado a la cita
        $surgerie->delete();

        return response()->json(['message' => 'Cirugia eliminada exitosamente'], 200);
    }
}
