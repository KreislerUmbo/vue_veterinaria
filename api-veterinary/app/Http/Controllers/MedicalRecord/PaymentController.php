<?php

namespace App\Http\Controllers\MedicalRecord;

use App\Exports\DownloadMedicalRecord;
use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecord\Payment\PaymentCollection;
use App\Http\Resources\MedicalRecord\Payment\PaymentResource;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPayment;
use App\Models\MedicalRecord;
use App\Models\Surgerie\Surgerie;
use App\Models\Surgerie\SurgeriePayment;
use App\Models\Vaccination\Vaccination;
use App\Models\Vaccination\VaccinationPayment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   //estos son los filtros que vienen del frontend
        $type_date = $request->type_date;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $state_pay = $request->state_pay;
        $state = $request->state;
        $specie = $request->specie;
        $search_pets = $request->search_pets;
        $search_vets = $request->search_vets;
        $type_service = $request->type_service; //  filtro para el tipo de servicio medico

        // Filtros de busqueda
        $medical_records = MedicalRecord::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets, $type_service)->orderBy("id", "desc")->paginate(25);
        return response()->json([
            "total_page" => $medical_records->lastPage(), // obtener el total de paginas
            "medical_records" => PaymentCollection::make($medical_records),
        ]);
    }


    public function downloadExcel(Request $request)
    {
        $type_date = $request->type_date;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $state_pay = $request->state_pay;
        $state = $request->state;
        $specie = $request->specie;
        $search_pets = $request->search_pets;
        $search_vets = $request->search_vets;
        $type_service = $request->type_service; //  filtro para el tipo de servicio medico

        $medical_records=MedicalRecord::filterMultiple($type_date, $start_date, $end_date, $state_pay, $state, $specie, $search_pets, $search_vets, $type_service)->orderBy("id", "desc")->get();
        return FacadesExcel::download(new DownloadMedicalRecord($medical_records),"listado_de_registro_medicos_reporte.xlsx");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $method_payment = $request->method_payment;
        $date_payment = $request->date_payment;
        $amount = $request->amount;
        $notes = $request->notes;


        //CUANDO ES UNA CITA MEDICA
        if ($request->appointment_id) {
            $appointment = Appointment::findOrFail($request->appointment_id);
            $amount_total_pay = $appointment->payments->sum('amount'); //suma de todos los pagos realizados

            //validar que el monto del pago no exceda el monto total de la cita medica
            if (($amount_total_pay + $amount) > $appointment->amount) {
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago excede el monto total pendiente de la cita médica(" . ($appointment->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }
            //actualizar el estado de la cita medica dependiendo del monto pagado
            if (($amount_total_pay + $amount) == $appointment->amount) {
                //actualizar el estado de la cita a pagado
                $appointment->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la cita a parcialmente/pendiente pagado
                $appointment->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }

            AppointmentPayment::create([
                "appointment_id" => $request->appointment_id,
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }
        //CUANDO ES UNA VACUNACION
        if ($request->vaccination_id) {

            $vaccination = Vaccination::findOrFail($request->vaccination_id);
            $amount_total_pay = $vaccination->payments->sum('amount'); //suma de todos los pagos realizados

            if (($amount_total_pay + $amount) > $vaccination->amount) {
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago excede el monto total pendiente de la vacunación(" . ($vaccination->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }
            //actualizar el estado de la vacunacion dependiendo del monto pagado
            if (($amount_total_pay + $amount) == $vaccination->amount) {
                //actualizar el estado de la vacunacion a pagado
                $vaccination->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la vacunacion a parcialmente/pendiente pagado
                $vaccination->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }

            VaccinationPayment::create([
                "vaccination_id" => $request->vaccination_id,
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }
        //CUANDO ES UNA CIRUGIA
        if ($request->surgerie_id) {
            $surgerie = Surgerie::findOrFail($request->surgerie_id);
            $amount_total_pay = $surgerie->payments->sum('amount'); //suma de todos los pagos realizados

            //validar que el monto del pago no exceda el monto total de la cirugia
            if (($amount_total_pay + $amount) > $surgerie->amount) {
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago excede el monto total pendiente de la cirugía(" . ($surgerie->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }
            //actualizar el estado de la cirugia dependiendo del monto pagado
            if (($amount_total_pay + $amount) == $surgerie->amount) {
                //actualizar el estado de la cirugia a pagado
                $surgerie->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la cirugia a parcialmente/pendiente pagado
                $surgerie->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }

            SurgeriePayment::create([
                "surgerie_id" => $request->surgerie_id,
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }
        $medical_record = MedicalRecord::findOrFail($request->medical_record_id);

        return response()->json(
            [
                "message" => 200,
                "payment" => PaymentResource::make($medical_record),
            ],
        );
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
        $method_payment = $request->method_payment;
        $date_payment = $request->date_payment;
        $amount = $request->amount;
        $notes = $request->notes;


        //CUANDO ES UNA CITA MEDICA
        if ($request->appointment_id) {
            $appointment = Appointment::findOrFail($request->appointment_id);
            $amount_total_pay = $appointment->payments->sum('amount'); //suma de todos los pagos realizados

            //validar que el monto del pago no exceda el monto total de la cita medica para eso
            // se resta el monto del pago actual y se suma el nuevo monto
            $appointment_payment = AppointmentPayment::findOrFail($id);
            $amount_courrent = $appointment_payment->amount;

            if ((($amount_total_pay - $amount_courrent) + $amount) > $appointment->amount) {
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago que quieres editar excede el monto total pendiente de la cita médica(" . ($appointment->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }
            //actualizar el estado de la cita medica dependiendo del monto pagado
            if ((($amount_total_pay - $amount_courrent) + $amount) == $appointment->amount) {
                //actualizar el estado de la cita a pagado
                $appointment->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la cita a parcialmente/pendiente pagado
                $appointment->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }


            $appointment_payment = AppointmentPayment::findOrFail($id);
            $appointment_payment->update([
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }
        //CUANDO ES UNA VACUNACION
        if ($request->vaccination_id) {

            $vaccination = Vaccination::findOrFail($request->vaccination_id);
            $amount_total_pay = $vaccination->payments->sum('amount'); //suma de todos los pagos realizados

            $vaccination_payment = VaccinationPayment::findOrFail($id);
            $amount_courrent = $vaccination_payment->amount;

            if ((($amount_total_pay - $amount_courrent) + $amount) > $vaccination->amount) { //si la suma del total pagado menos el monto actual del pago mas el nuevo monto excede el monto total de la vacunacion
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago que quieres editar excede el monto total pendiente de la vacunación(" . ($vaccination->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }

            //actualizar el estado de la vacunacion dependiendo del monto pagado
            if ((($amount_total_pay - $amount_courrent) + $amount) == $vaccination->amount) {
                //actualizar el estado de la vacunacion a pagado
                $vaccination->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la vacunacion a parcialmente/pendiente pagado         
                $vaccination->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }

            $vaccination_payment = VaccinationPayment::findOrFail($id);

            $vaccination_payment->update([
                "vaccination_id" => $request->vaccination_id,
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }
        //CUANDO ES UNA CIRUGIA
        if ($request->surgerie_id) {
            $surgerie = Surgerie::findOrFail($request->surgerie_id);
            $amount_total_pay = $surgerie->payments->sum('amount'); //suma de todos los pagos realizados

            //validar que el monto del pago no exceda el monto total de la cirugia
            $surgerie_payment = SurgeriePayment::findOrFail($id);
            $amount_courrent = $surgerie_payment->amount;

            if ((($amount_total_pay - $amount_courrent) + $amount) > $surgerie->amount) {
                return response()->json(
                    [
                        "message" => 403,
                        "message_text" => "El monto del pago excede el monto total pendiente de la cirugía(" . ($surgerie->amount - $amount_total_pay) . " PEN).",
                    ],
                );
            }

            //actualizar el estado de la cirugia dependiendo del monto pagado
            if ((($amount_total_pay - $amount_courrent) + $amount) == $surgerie->amount) {
                //actualizar el estado de la cirugia a pagado
                $surgerie->update([
                    "state_pay" => 3, //pagado
                ]);
            } else {
                //actualizar el estado de la cirugia a parcialmente/pendiente pagado            
                $surgerie->update([
                    "state_pay" => 2, //pendiente de pago
                ]);
            }


            $surgerie_payment = SurgeriePayment::findOrFail($id);
            $surgerie_payment->update([
                "surgerie_id" => $request->surgerie_id,
                "method_payment" => $request->method_payment,
                "date_payment" => $request->date_payment,
                "amount" => $request->amount,
                "notes" => $request->notes,
                "user_id" => auth('api')->user()->id,
            ]);
        }

        $medical_record = MedicalRecord::findOrFail($request->medical_record_id);

        return response()->json(
            [
                "message" => 200,
                "payment" => PaymentResource::make($medical_record),
            ],
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->get("appointment_id")) {
            $appointment_payment = AppointmentPayment::findOrFail($id);
            //actualizar el estado de la cita medica a pendiente de pago
            $appointment_payment->appointment->update([
                "state_pay" => 2, //pendiente de pago
            ]);
            $appointment_payment->delete();
        }


        if ($request->get("vaccination_id")) {
            $vaccination_payment = VaccinationPayment::findOrFail($id);
            //actualizar el estado de la vacunacion a pendiente de pago
            $vaccination_payment->vaccination->update([
                "state_pay" => 2, //pendiente de pago
            ]);
            $vaccination_payment->delete();
        }


        if ($request->get("surgerie_id")) {
            $surgerie_payment = SurgeriePayment::findOrFail($id);
            //actualizar el estado de la cirugia a pendiente de pago
            $surgerie_payment->surgerie->update([
                "state_pay" => 2, //pendiente de pago
            ]);
            $surgerie_payment->delete();
        }

        $medical_record = MedicalRecord::findOrFail($request->get("medical_record_id"));
        return response()->json(
            [
                "message" => 200,
                "payment" => PaymentResource::make($medical_record),
            ],
        );
    }
}
