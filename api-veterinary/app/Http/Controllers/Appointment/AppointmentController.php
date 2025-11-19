<?php

namespace App\Http\Controllers\Appointment;

use App\Exports\DownloadAppointment;
use App\Http\Controllers\Controller;
use App\Http\Resources\Appointment\AppointmentCollection;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPayment;
use App\Models\Appointment\AppointmentSchedule;
use App\Models\MedicalRecord;
use App\Models\Pets\Pet;
use App\Models\Surgerie\Surgerie;
use App\Models\Vaccination\Vaccination;
use App\Models\Veterinarie\VeterinarieScheduleDay;
use App\Models\Veterinarie\VeterinarieScheduleJoin;
use Carbon\Carbon;
use Faker\Provider\Medical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type_date = $request->type_date;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $state_pay = $request->state_pay;
        $state = $request->state;
        $specie = $request->specie;
        $search_pets = $request->search_pets;
        $search_vets = $request->search_vets;

        // Filtros de busqueda
        $appointments = Appointment::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $appointments->lastPage(),
            "appointments" => AppointmentCollection::make($appointments),
        ]);
    }

    public function filter(Request $request) // esta funcion es para filtrar la disponibilidad de los veterinarios por fecha y hora
    {
        $date_appointment = $request->input('date_appointment');
        if (!$date_appointment) {
            $date_appointment = $request->vaccionation_date; // en caso de que no venga la fecha de la cita medica, se toma la fecha de la vacunacion
            if (!$date_appointment) {
                $date_appointment = $request->surgerie_date; // en caso de que no venga la fecha de la cita medica ni de la vacunacion, se toma la fecha de la cirugia
            }
        }
        $hour = $request->input('hour');

        // 1.- obtnerr el nombre del dia de la fecha que hemos seleccionado
        date_default_timezone_set('America/Lima');
        Carbon::setLocale('es');
        $dayName = Carbon::parse($date_appointment)->dayName;

        // 2.- Obtner la lista de los veterinarios que atiendan ese dia (schedule_days)
        $veterinarie_days = VeterinarieScheduleDay::where("day", "ilike", "%" . $dayName . "%")->orderBy("veterinarie_id", "asc")->get();

        // 3.- Obtner los segmentos de tiempos o hora del dia de atencion
        $veternarie_time_availability = collect([]);
        foreach ($veterinarie_days as $key => $veterinarie_day) {
            $segment_times_formats = collect([]);
            $segment_time_joins = VeterinarieScheduleJoin::where("veterinarie_schedule_day_id", $veterinarie_day->id)
                ->where(function ($query) use ($hour) {
                    // Filtrar por hora si se proporciona
                    if ($hour) {
                        $query->whereHas("veterinarie_schedule_hour", function ($q) use ($hour) {
                            $hour_explode = explode(":", $hour);
                            $q->where("hour", $hour_explode[0]);
                        });
                    }
                })
                ->get();

            foreach ($segment_time_joins as $segment_time_join) {
                // verificar si el veterinario ya tiene una cita en ese dia y en ese segmento de tiempo o hora
                $check = Appointment::whereDate('date_appointment', $date_appointment)
                    ->where('state', '<>', 2) // la cita no debe estar cancelada
                    ->where('veterinarie_id', $veterinarie_day->veterinarie_id) // filtrar por el veterinario
                    ->whereHas('schedules', function ($q) use ($segment_time_join) { // filtrar por el segmento de tiempo o hora
                        $q->where('veterinarie_schedule_hour_id', $segment_time_join->veterinarie_schedule_hour_id);
                    })->first();
                // si no se encuentra una cita, verificar en las vacunaciones
                if (!$check) {
                    $check = Vaccination::whereDate('vaccionation_date', $date_appointment)
                        ->where('state', '<>', 2) // la cita no debe estar cancelada
                        ->where('veterinarie_id', $veterinarie_day->veterinarie_id) // filtrar por el veterinario
                        ->whereHas('schedules', function ($q) use ($segment_time_join) { // filtrar por el segmento de tiempo o hora
                            $q->where('veterinarie_schedule_hour_id', $segment_time_join->veterinarie_schedule_hour_id);
                        })->first();


                    if (!$check) {
                        // si se encuentra una cita o vacunacion, verificar en las cirugias
                        $check = $check = Surgerie::whereDate('surgerie_date', $date_appointment)
                            ->where('state', '<>', 2) // la cirugia no debe estar cancelada
                            ->where('veterinarie_id', $veterinarie_day->veterinarie_id) // filtrar por el veterinario
                            ->whereHas('schedules', function ($q) use ($segment_time_join) { // filtrar por el segmento de tiempo o hora
                                $q->where('veterinarie_shedule_hour_id', $segment_time_join->veterinarie_schedule_hour_id);
                            })->first();
                    }
                }
                // formatear los segmentos de tiempo o hora
                $segment_times_formats->push([
                    "id" => $segment_time_join->id,
                    "veterinarie_schedule_day_id" => $segment_time_join->veterinarie_schedule_day_id,
                    "veterinarie_schedule_hour_id" => $segment_time_join->veterinarie_schedule_hour_id,
                    "hour" => $segment_time_join->veterinarie_schedule_hour->hour,
                    "schedule_hour" => [
                        "hour_start" => $segment_time_join->veterinarie_schedule_hour->hour_start,
                        "hour_end" => $segment_time_join->veterinarie_schedule_hour->hour_end,
                        "hour" => $segment_time_join->veterinarie_schedule_hour->hour,
                        "hour_star_format" => Carbon::parse(date("Y-m-d") . ' ' . $segment_time_join->veterinarie_schedule_hour->hour_start)->format("h:i A"),
                        "hour_end_format" => Carbon::parse(date("Y-m-d") . ' ' . $segment_time_join->veterinarie_schedule_hour->hour_end)->format("h:i A"),
                    ],
                    "check" => $check ? true : false, // si el veterinario tiene una cita en ese dia y en ese segmento de tiempo o hora
                ]);
            }

            // 4.- Agrupacion de los segmentos de tiempo por hora
            $segment_time_groups = collect([]);
            foreach ($segment_times_formats->groupBy("hour") as $hourT => $segment_time_format) {
                $count_availability = $segment_time_format->where("check", false)->count(); // contar los segmentos de tiempo disponibles (check = false)

                $segment_time_groups->push([
                    "hour" => $hourT,
                    "hour_format" => Carbon::parse(date("Y-m-d") . ' ' . $hourT . ':00:00')->format("h:i A"),
                    "segment_times" => $segment_time_format,
                    "count_availability" => $count_availability, // contar los segmentos de tiempo disponibles (check = false)
                ]);
            }
            if ($segment_time_groups->count() !== 0) {

                $veternarie_time_availability->push([
                    "id" => $veterinarie_day->veterinarie_id,
                    "full_name" => $veterinarie_day->veterinarie->name . ' ' . $veterinarie_day->veterinarie->surname,
                    "segment_time_groups" => $segment_time_groups,
                ]);
            }
        }

        return response()->json([
            "veternarie_time_availability" => $veternarie_time_availability,
        ]);
    }

    public function downloadExcel(Request $request) // esta funcion es para descargar el excel de las citas
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
        $appointments = Appointment::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets)->orderBy("id", "desc")->get();

        // Descargar el excel
        return Excel::download(new DownloadAppointment($appointments), 'citas_medicas_reporte.xlsx');
    }

    public function searchPets($search) // esta funcion es para buscar las mascotas por su nombre o por nombre del dueño o numero de documento
    {
        $pets = Pet::whereHas("owner", function ($q) use ($search) {
            $q->where(DB::raw("pets.name ||' '|| owners.first_name || ' ' || COALESCE(owners.last_name,'') || ' ' || owners.n_document || '' || owners.phone"), "ilike", "%" . $search . "%");
        })->limit(10)->get();

        return  response()->json([
            "pets" => $pets->map(function ($pet) {
                return [
                    "id" => $pet->id,
                    "name" => $pet->name,
                    "specie" => $pet->specie,
                    "breed" => $pet->breed,
                    "owner" => [
                        "id" => $pet->owner->id,
                        "first_name" => $pet->owner->first_name,
                        "last_name" => $pet->owner->last_name,
                        "phone" => $pet->owner->phone,
                        "n_document" => $pet->owner->n_document,
                    ]
                ];
            })
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        date_default_timezone_set('America/Lima');
        Carbon::setLocale('es');

        $dayName = Carbon::parse($request->date_appointment)->dayName;

        $appointment =    Appointment::create([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            'date_appointment' => $request->input('date_appointment'),
            'reason' => $request->reason,
            'user_id' => auth('api')->user()->id,
            'amount' => $request->amount,
            'state_pay' => $request->state_pay,
        ]);

        MedicalRecord::create([
            'pet_id' => $request->pet_id,
            'veterinarie_id' => $request->veterinarie_id,
            'event_type' => 1, // 1=cita medica, 2=vacunacion, 3=cirugia
            'appointment_id' => $appointment->id,
            'event_date' => $request->input('date_appointment'),
            'notes' => 'Cita médica creada',
        ]);

        AppointmentPayment::create([
            'appointment_id' => $appointment->id,
            'method_payment' => $request->method_payment,
            'amount' => $request->adelanto,
            'date_payment' => $request->date_appointment,
        ]);

        foreach ($request->selected_segment_times as $key => $selected_segment_time) { // recorrer los horarios seleccionados 
            AppointmentSchedule::create([
                'appointment_id' => $appointment->id, // id de la cita
                'veterinarie_schedule_hour_id' => $selected_segment_time["segment_time_id"], // id del segmento de tiempo
            ]);
        }

        return response()->json(['message' => 'Cita creada exitosamente'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) // mostrar una cita en especifico
    {
        $appointment = Appointment::findOrFail($id); // buscar la cita por su id, el findOrFail lanza una excepcion si no encuentra la cita
        return response()->json([
            "appointment" => AppointmentResource::make($appointment),
        ]);
    }

    /**
     * la funcion update editar una cita
     */
    public function update(Request $request, string $id)
    {
        date_default_timezone_set('America/Lima'); // establecer la zona horaria
        Carbon::setLocale('es'); // establecer el idioma a español

        // obtener el nombre del dia de la fecha seleccionada
        $dayName = Carbon::parse($request->date_appointment)->dayName;

        $appointment = Appointment::findOrFail($id); // buscar la cita por su id

        if ($appointment->state == 3) { // si la cita ya fue atendida no se puede editar
            return response()->json([
                "message" =>  403,
                "message_text" => "No se puede editar esta cita médica porque ya ha sido atendida."
            ]);
        }
        // Validar que el monto no sea menor al adelanto ya pagado
        //$request->amount es el monto del costo de la cita que se quiere actualizar
        // $appointment->payments->sum('amount') es la suma de los adelantos ya pagados que vienen en la relacion payments de la cita
        if ($request->amount < $appointment->payments->sum('amount')) { // si el monto es menor al adelanto no se puede editar
            return response()->json([
                "message" => 403,
                "message_text" => "El monto no puede ser menor al adelanto (" . $appointment->payments->sum('amount') . "PEN) ya pagado",
            ]);
        }

        // Actualizar los datos de la cita
        $appointment->update([
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
            'day' => $dayName,
            //'date_appointment' => $request->input('date_appointment'),
            'reason' => $request->reason,
            'amount' => $request->amount,
            'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
            //'state_pay' => $request->state_pay,  
        ]);

        $appointment->medical_record->update([ // actualizar el registro medico relacionado a la cita
            'veterinarie_id' => $request->veterinarie_id,
            'pet_id' => $request->pet_id,
        ]);

        // Actualizar el estado del pago de la cita
        if ($request->amount == $appointment->payments->sum('amount')) {
            $appointment->update([
                'state_pay' => 3, // 1=pendiente, 2=con adelanto, 3=completo
            ]);
        } else {
            $appointment->update([
                'state_pay' => 2, // 1=pendiente, 2=con adelanto, 3=completo
            ]);
        }

        // Actualizar la fecha de la cita solo si se proporciona en la solicitud
        if ($request->date_appointment) {
            $appointment->update([
                'date_appointment' => $request->input('date_appointment'),
                'reprogramar' => 1, //si se cambia la fecha de la cita o las horas, se marca como reprogramada
            ]);
            $appointment->medical_record->update([ // actualizar el registro medico relacionado a la cita
                'event_date' => $request->input('date_appointment'),
                'notes' => 'Cita médica reprogramada',
            ]);
        }

        if (sizeof($request->selected_segment_times) > 0) { //sizeof es para saber el tamaño de un array
            // Eliminar los horarios de la cita existentes
            foreach ($appointment->schedules as $key => $schedule) {
                $schedule->delete(); // eliminar los horarios de la cita
            }
            // Agregar los nuevos horarios seleccionados
            foreach ($request->selected_segment_times as $key => $selected_segment_time) {
                AppointmentSchedule::create([
                    'appointment_id' => $appointment->id,
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
        $appointment = Appointment::findOrFail($id);
        if ($appointment->state == 3) {
            return response()->json(['message' =>  403]); // si la cita ya fue atendida no se puede eliminar
        }

        $appointment->medical_record->delete(); // eliminar el registro medico relacionado a la cita
        $appointment->delete();

        return response()->json(['message' => 'Cita eliminada exitosamente'], 200);
    }
}
