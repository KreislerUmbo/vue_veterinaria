<?php

namespace App\Http\Controllers\MedicalRecord;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecord\Calendar\MedicalRecordCalendarCollection;
use App\Http\Resources\MedicalRecord\Calendar\MedicalRecordCalendarResource;
use App\Http\Resources\MedicalRecord\MedicalRecordPetCollection;
use App\Http\Resources\PetsResource;
use App\Models\Appointment\Appointment;
use App\Models\MedicalRecord;
use App\Models\Pets\Pet;
use Carbon\Carbon;
use Faker\Provider\Medical;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pet_id = $request->pet_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
  
        $pet = Pet::findOrFail($pet_id);
        $medical_records = MedicalRecord::where("pet_id", $pet_id)->where(function ($q) use ($start_date, $end_date) {
                if ($start_date && $end_date) {
                    $q->whereBetween("event_date", [Carbon::parse($start_date)->format("Y-m-d") . " 00:00:00", Carbon::parse($end_date)->format("Y-m-d") . " 23:59:59"]);
                }
            })->orderBy("id", "desc")->get();
        return response()->json([
            "pet" => PetsResource::make($pet),
            "historial_records" => MedicalRecordPetCollection::make($medical_records),
        ]);
    }

    public function calendar(Request $request)
    {
        $medical_records = MedicalRecord::orderBy("id", "DESC")->get();
        return response()->json([
            "calendars" => MedicalRecordCalendarCollection::make($medical_records),
        ]);
    }


    public function update_aux(Request $request, string $id) //
    {
        $medical_record = MedicalRecord::findOrFail($id);

        if ($medical_record->appointment_id) { //si el registro medico tiene una cita asociada
            $medical_record->appointment->update([
                'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
            ]);
        }
        if ($medical_record->vaccination_id) { //si el registro medico tiene una vacunacion asociada
            $medical_record->vaccination->update([
                'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
            ]);
        }
        if ($medical_record->surgerie_id) { //si el registro medico tiene una cirugia asociada
            $medical_record->surgerie->update([
                'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
                'outcome' => $request->notes, // resultado de la cirugia
            ]);
        }

        // error_log($request->notes);
        $medical_record->update([
            'notes' => $request->notes,
        ]);
        return response()->json([
            "event" => MedicalRecordCalendarResource::make($medical_record)
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
