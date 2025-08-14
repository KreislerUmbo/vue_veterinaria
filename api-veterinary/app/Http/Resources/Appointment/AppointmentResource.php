<?php

namespace App\Http\Resources\Appointment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "veterinarie_id" => $this->resource->veterinarie_id,
            "pet_id" => $this->resource->pet_id,
            "day" => $this->resource->day,
            "date_appointment" => $this->resource->date_appointment,
            "reason" => $this->resource->reason,
            "reprogramar" => $this->resource->reprogramar,
            "status" => $this->resource->status,
            "user_id" => $this->resource->user_id,
            "amount" => $this->resource->amount,
            "state_pay" => $this->resource->state_pay,
        ];
    }
}
