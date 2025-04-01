<?php

namespace App\Http\Controllers\Veterinarie;

use App\Http\Controllers\Controller;
use App\Models\Veterinarie\VeterinarieScheduleHour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class VeterinarieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    public function config()
    {
        $roles = Role::where("name", "ilike", "%veterinario%")->get();
        $schedule_hours = VeterinarieScheduleHour::all();

        $schedule_hours_group = collect([]);

        foreach ($schedule_hours->groupBy("hour") as $key => $schedule_hour) {
            $schedule_hours_group->push([
                "hour" => $key,
                "hour_format" => Carbon::parse(date("Y-m-d") . ' ' . $key.':00:00')->format("h:i A"),
                "segments_time" => $schedule_hour->map(function ($schedule_h) {
                    return [
                        "id" => $schedule_h->id,
                        "hour_start" => $schedule_h->hour_start,
                        "hour_end" => $schedule_h->hour_end,
                        "hour" => $schedule_h->hour,
                        "hour_start_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule_h->hour_start)->format("h:i A"),
                        "hour_end_format" =>  Carbon::parse(date("Y-m-d") . ' ' . $schedule_h->hour_end)->format("h:i A"),
                    ];
                })
            ]);
        }
        return response()->json([
            "roles" => $roles,
            "schedule_hours_group" => $schedule_hours_group,
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
