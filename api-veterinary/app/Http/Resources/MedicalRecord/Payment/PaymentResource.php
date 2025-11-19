<?php

namespace App\Http\Resources\MedicalRecord\Payment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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

        return [
            "id" => $this->resource->id,
            "veterinarie_id" => $resource->veterinarie_id,
            "veterinarie" => [
                "full_name" => $resource->veterinarie->name . ' ' . $resource->veterinarie->surname,
                "role" => [
                    "name" => $resource->veterinarie->role->name,
                ]
            ],
            "pet_id" => $this->resource->pet_id,
            "pet" => [
                "id" => $resource->pet->id,
                "name" => $resource->pet->name,
                "specie" => $resource->pet->specie,
                "breed" => $resource->pet->breed,
                "photo" => env("APP_URL") . "storage/" . $resource->pet->photo,
                "owner" => [
                    "first_name" => $resource->pet->owner->first_name,
                    "last_name" => $resource->pet->owner->last_name,
                    "n_document" => $resource->pet->owner->n_document,
                    "phone" => $resource->pet->owner->phone,
                ],
            ],

            "event_date" => Carbon::parse($this->event_date)->format('Y-m-d'),
            "notes" => $this->resource->notes,
            "created_at" => $this->resource->created_at->format('Y-m-d H:i:s'),
            "event_type" => $this->event_type,

            "appointment_id" => $this->resource->appointment_id,
            "vaccination_id" => $this->resource->vaccination_id,
            "surgerie_id" => $this->resource->surgerie_id,

            "state" => $resource->state,
            "amount" => $resource->amount,
            "state_pay" => $resource->state_pay,
            "payment_total" => $resource->payments->sum('amount'), //suma de todos los pagos realizados para ese servicio medico

            "payments" => $resource->payments->map(function ($payment) {
                return [
                    "id" => $payment->id,
                    "amount" => $payment->amount,
                    "date_payment" => Carbon::parse($payment->date_payment)->format('Y-m-d'),
                    "method_payment" => $payment->method_payment,
                    "notes" => $payment->notes,
                ];
            }),
        ];
    }
}
