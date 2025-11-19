<?php

namespace App\Http\Resources\Surgerie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurgerieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $schedules = $this->resource->schedules->map(function ($schedule) {//map es para recorrer los horarios de la vacunacion
            return [
                "id" => $schedule->id,
                "veterinarie_schedule_hour_id" => $schedule->veterinarie_schedule_hour_id,
                "hour" => $schedule->schedule_hour->hour,
                "schedule_hour" => [
                    "hour_start" => $schedule->schedule_hour->hour_start,
                    "hour_end" => $schedule->schedule_hour->hour_end,
                    "hour" => $schedule->schedule_hour->hour,
                    "hour_start_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule->schedule_hour->hour_start)->format('h:i A'),
                    "hour_end_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule->schedule_hour->hour_end)->format('h:i A'),
                ],
            ];
        });
        $schedule_for_hour=collect([]);
        
        // agrupar los horarios por hora
        foreach($schedules->groupBy("hour") as $key=> $segment_times){//  foreach($schedules->groupBy("hour")  recorre los horarios agrupados por hora, el key es la hora y el segment_times son los horarios que pertenecen a esa hora
            $is_complete=$segment_times->count()==2 ? true:false;
            $schedule_for_hour->push([
                "hour"=>$key,
                "hour_format"=>Carbon::parse(date("Y-m-d") . ' ' . $key.':00:00')->format('h:i A'),//formatear la hora
                "segments_time"=>$segment_times,//los horarios que pertenecen a esa hora
                "is_complete"=>$is_complete,
            ]);
        }
        return [
            "id" => $this->resource->id,
            "veterinarie_id" => $this->resource->veterinarie_id,
            "veterinarie" => [
                "full_name" => $this->resource->veterinarie->name . ' ' . $this->resource->veterinarie->surname,
                "role" => [
                    "name" => $this->resource->veterinarie->role->name,
                ]
            ],
            "pet_id" => $this->resource->pet_id,
            "pet" => [
                "id" => $this->resource->pet->id,
                "name" => $this->resource->pet->name,
                "specie" => $this->resource->pet->specie,
                "breed" => $this->resource->pet->breed,
                "photo" => env("APP_URL") . "storage/" . $this->resource->pet->photo,
                "owner" => [
                    "first_name" => $this->resource->pet->owner->first_name,
                    "last_name" => $this->resource->pet->owner->last_name,
                    "n_document" => $this->resource->pet->owner->n_document,
                    "phone" => $this->resource->pet->owner->phone,
                ],
            ],
            "day" => $this->resource->day,
            "surgerie_date" => Carbon::parse($this->resource->surgerie_date)->format('Y-m-d'),
            "medical_notes" => $this->resource->medical_notes,
            "reprogramar" => $this->resource->reprogramar,
            "surgerie_type" => $this->resource->surgerie_type,
            "state" => $this->resource->state,//1 pendiente, 3 atendido, 2 cancelado
            "outside" => $this->resource->outside,
            "outcome" => $this->resource->outcome,
            "user_id" => $this->resource->user_id,
            "user" => [
                "full_name" => $this->resource->user->name . ' ' . $this->resource->user->surname,
            ],

            "amount" => $this->resource->amount,
            "state_pay" => $this->resource->state_pay,
            "created_at" => $this->resource->created_at->format('Y-m-d H:i:s'),

            "payments" => $this->resource->payments->map(function ($payment) {
                return [
                    "id" => $payment->id,
                    "amount" => $payment->amount,
                    "date_payment" => Carbon::parse($payment->date_payment)->format('Y-m-d'),
                    "method_payment" => $payment->method_payment,
                ];
            }),

            "schedules" => $this->resource->schedules->map(function ($schedule) {
                return [
                    "id" => $schedule->id,
                    "veterinarie_schedule_hour_id" => $schedule->veterinarie_schedule_hour_id,
                    "hour" => $schedule->schedule_hour->hour,
                    "schedule_hour" => [
                        "hour_start" => $schedule->schedule_hour->hour_start,
                        "hour_end" => $schedule->schedule_hour->hour_end,
                        "hour" => $schedule->schedule_hour->hour,
                        "hour_start_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule->schedule_hour->hour_start)->format('h:i A'),
                        "hour_end_format" => Carbon::parse(date("Y-m-d") . ' ' . $schedule->schedule_hour->hour_end)->format('h:i A'),
                    ],
                ];
            }),
            "schedule_for_hour"=> $schedule_for_hour->sortBy("hour")->values()->all(),// ordenar los horarios por hora
            "schedules"=>$schedules->sortBy("veterinarie_schedule_hour_id")->values()->all(),//sortBy es para ordenar el array por el id del horario
        ];
    }
}
