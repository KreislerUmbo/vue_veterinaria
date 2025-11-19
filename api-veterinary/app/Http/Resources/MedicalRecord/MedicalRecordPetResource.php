<?php

namespace App\Http\Resources\MedicalRecord;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordPetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = null;
        if ($this->resource->appointment_id) {
            $resource = $this->resource->appointment;
        }
        if ($this->resource->vaccination_id) {
            $resource = $this->resource->vaccination;
        }
        if ($this->resource->surgerie_id) {
            $resource = $this->resource->surgerie;
        }

        $hour_start = "";
        $hour_end = "";

        $schedule_hour_start = $resource->schedules->sortBy("veterinarie_shedule_hour_id")->first();
        $schedule_hour_end = $resource->schedules->sortBy("veterinarie_shedule_hour_id")->last();
        if ($schedule_hour_start) {
            $hour_start = Carbon::parse(date("Y-m-d") . " " . $schedule_hour_start->schedule_hour->hour_start)->format("h:i A");
        }
        if ($schedule_hour_end) {
            $hour_end = Carbon::parse(date("Y-m-d") . " " . $schedule_hour_end->schedule_hour->hour_end)->format("h:i A");
        }

        return [
            "id" => $this->resource->id,
            "veterinarie_id" => $resource->veterinarie_id,
            "veterinarie" => [
                "full_name" => $this->resource->veterinarie->name . ' ' . $this->resource->veterinarie->surname,
                "role" => [
                    "name" => $this->resource->veterinarie->role->name,
                ],
                "designation" => $this->resource->veterinarie->designation,
            ],

            "event_date" => Carbon::parse($this->event_date)->format('Y-m-d'),
            "notes" => $this->resource->notes,
            "created_at" => $this->resource->created_at->diffForHumans(), //diffForHumans sirve para indicar el tiempo que paso ddesde que se creo el registro
            "event_type" => $this->event_type,

            "appointment_id" => $this->resource->appointment_id,
            "vaccination_id" => $this->resource->vaccination_id,
            "surgerie_id" => $this->resource->surgerie_id,

            "state" => $resource->state,
            "amount" => $resource->amount,
            "state_pay" => $resource->state_pay,
            "payment_total" => $resource->payments->sum('amount'), //suma de todos los pagos realizados para ese servicio medico

        ];
    }
}
