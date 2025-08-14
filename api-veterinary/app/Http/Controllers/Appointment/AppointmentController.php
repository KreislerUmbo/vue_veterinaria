<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Pets\Pet;
use App\Models\Veterinarie\VeterinarieScheduleDay;
use App\Models\Veterinarie\VeterinarieScheduleJoin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function filter(Request $request)
    {
        $date_appointment = $request->input('date_appointment');
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
            $segment_time_joins = VeterinarieScheduleJoin::where("veterinarie_schedule_day_id", $veterinarie_day->id)->get();

            foreach ($segment_time_joins as $segment_time_join) {
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
                    ]
                ]);
            }

            // 4.- Agrupacion de los segmentos de tiempo por hora
            $segment_time_groups = collect([]);
            foreach ($segment_times_formats->groupBy("hour") as $hour => $segment_time_format) {

                $segment_time_groups->push([
                    "hour" => $hour,
                    "hour_format" => Carbon::parse(date("Y-m-d") . ' ' . $hour . ':00:00')->format("h:i A"),
                    "segment_times" => $segment_time_format
                ]);
            }
            $veternarie_time_availability->push([
                "id" => $veterinarie_day->veterinarie_id,
                "full_name" => $veterinarie_day->veterinarie->name.' '. $veterinarie_day->veterinarie->surname,
                "segment_time_groups" => $segment_time_groups,
            ]);
        }

        return response()->json([
            "veternarie_time_availability" => $veternarie_time_availability,
        ]);
    }


    public function searchPets($search)
    {
        $pets = Pet::whereHas("owner", function ($q) use ($search) {
            $q->where(DB::raw("pets.name ||' '|| owners.first_name || ' ' || COALESCE(owners.last_name,'') || ' ' || owners.n_document || '' || owners.phone"), "ilike", "%" . $search . "%");
        })->get();

        return  response()->json([
            "pets" => $pets->map(function ($pet) {
                return [
                    "id" => $pet->id,
                    "name" => $pet->name,
                    "specie" => $pet->specie,
                    "breed" => $pet->breed,
                    "owner" => [
                        "id" => $pet->owner()->id,
                        "first_name" => $pet->owner()->first_name,
                        "last_name" => $pet->owner()->last_name,
                        "phone" => $pet->owner()->phone,
                        "n_document" => $pet->owner()->n_document,
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
