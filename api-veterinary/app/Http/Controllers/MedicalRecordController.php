<?php

namespace App\Http\Controllers;

use App\Http\Resources\MedicalRecord\Calendar\MedicalRecordCalendarCollection;
use App\Http\Resources\MedicalRecord\Calendar\MedicalRecordCalendarResource;
use App\Models\Appointment\Appointment;
use Faker\Provider\Medical;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function calendar(Request $request)
    {
        $appointments = Appointment::orderBy("id", "DESC")->get();
        return response()->json([
            "calendars" => MedicalRecordCalendarCollection::make($appointments),
        ]);
    }

    public function update_aux(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id); // buscar la cita por su id
        $appointment->update([
            'state' => $request->state, // 1=pendiente, 2=reprogramada, 3=atendida, 4=cancelada
        ]);
        $appointment->medical_record->update([ // actualizar el registro medico relacionado a la cita
            'notes' => $request->notes,
        ]);
        return response()->json([
            "event" => MedicalRecordCalendarResource::make($appointment)
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
