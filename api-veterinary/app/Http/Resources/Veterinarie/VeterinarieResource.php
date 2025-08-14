<?php

namespace App\Http\Resources\Veterinarie;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VeterinarieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $selected_segment_times = [];
        foreach ($this->resource->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                array_push($selected_segment_times, $schedule_join->veterinarie_schedule_hour_id . '-' . $schedule_day->day);
            }
        }

        $schedule_hours_veterinrie = [];
        foreach ($this->resource->schedule_days as $schedule_day) {
            foreach ($schedule_day->schedule_joins as $schedule_join) {
                array_push($schedule_hours_veterinrie, [
                    'id_seg'=>$schedule_join->veterinarie_schedule_hour_id . '-' . $schedule_day->day,
                    'segment_time_id'=>$schedule_join->veterinarie_schedule_hour_id ,
                    'day' => $schedule_day->day,
                ]);
            }
        }


        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'surname' => $this->resource->surname,
            'full_name' => $this->resource->name . ' ' . $this->resource->surname,
            'email' => $this->resource->email,
            'gender' => $this->resource->gender,
            'role_id' => $this->resource->role_id,
            'role' => [
                'id' => $this->resource->role->id,
                'name' => $this->resource->role->name
            ],
            'avatar' => $this->resource->avatar ? env("APP_URL") . "storage/" . $this->resource->avatar : null,
            'phone' => $this->resource->phone,
            'type_document' => $this->resource->type_document,
            'n_document' => $this->resource->n_document,
            'birthday' => $this->resource->birthday ? Carbon::parse($this->resource->birthday)->format("Y/m/d") : null,
            'designation' => $this->resource->designation,
            'selected_segment_times' => $selected_segment_times,
            'schedule_hours_veterinrie' => $schedule_hours_veterinrie,
        ];
    }
}
