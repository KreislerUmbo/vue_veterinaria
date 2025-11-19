<table style="border: 1px">
    <thead>
        <tr>
            <th>#</th>
            <th width="40">Mascota</th>
            <th width="20">Especie</th>
            <th width="40">Veterinario</th>
            <th width="20">Tipo Servicio</th>
            <th width="20">Fecha del Servicio</th>
            <th width="20">Estado del Servicio</th>
            <th width="20">Estado del Pago Servicio</th>
            <th width="20">Costo del Servicio</th>
            <th width="20">Monto Cancelado</th>
            <th width="20">Estado Pago</th>
            <th width="40">Horario</th>

        </tr>
    </thead>
    <tbody>

        @foreach ($medical_records_data as $key => $medical_record)
            @php
                $resource = null;
                if ($medical_record->appointment_id) {
                    $resource = $medical_record->appointment;
                }
                if ($medical_record->vaccination_id) {
                    $resource = $medical_record->vaccination;
                }
                if ($medical_record->surgerie_id) {
                    $resource = $medical_record->surgerie;
                }
            @endphp
            {{ count($medical_records_data) }}
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $medical_record->pet->name }}</td>
                <td>{{ $medical_record->pet->specie }}</td>
                <td>{{ $medical_record->veterinarie->name . ' ' . $medical_record->veterinarie->surname }}</td>
                <td>
                    @php
                        $type_service = '';
                        switch ($medical_record->event_type) {
                            case 1:
                                $type_service = 'Citas Médicas';
                                break;
                            case 2:
                                $type_service = 'Vacunas';
                                break;
                            case 3:
                                $type_service = 'Cirugías';
                                break;

                            default:
                                # code...
                                break;
                        }
                    @endphp
                    {{ $type_service }}
                </td>
                <td>{{ Carbon\Carbon::parse($medical_record->event_date)->format('d/m/Y') }}</td>
                @php
                    $state_service = '';
                    switch ($medical_record->state) {
                        case 1:
                            $state_service = 'Pendiente';
                            break;
                        case 2:
                            $state_service = 'Cancelada';
                            break;
                        case 3:
                            $state_service = 'Atendida';
                            break;
                        default:
                            # code...
                            break;
                    }
                @endphp
                {{-- Cambiar color de fondo segun el estado de la cita --}}
                @if ($resource->state == 1)
                    <td style="background: #fbff18;">
                        {{ $state_service }}
                    </td>
                @endif

                @if ($resource->state == 2)
                    <td style="background: #f50808;">
                        {{ $state_service }}
                    </td>
                @endif

                @if ($resource->state == 3)
                    <td style="background: #0efa06;">
                        {{ $state_service }}
                    </td>
                @endif
                {{-- Fin cambiar color de fondo segun el estado de la cita --}}
                {{-- Estado de pago --}}

                @php
                    $state_payment = '';
                    switch ($resource->state_pay) {
                        case 1:
                            $state_payment = 'Pendiente';
                            break;
                        case 2:
                            $state_payment = 'Parcial';
                            break;
                        case 3:
                            $state_payment = 'Completado';
                            break;
                        default:
                            # code...
                            break;
                    }
                @endphp
                {{-- Cambiar color de fondo segun el estado del pago --}}
                @if ($resource->state_pay == 1)
                    <td style="background: #fc72be;">
                        {{ $state_payment }}
                    </td>
                @endif

                @if ($resource->state_pay == 2)
                    <td style="background: #81cef1;">
                        {{ $state_payment }}
                    </td>
                @endif

                @if ($resource->state_pay == 3)
                    <td style="background: #a1f55d;">
                        {{ $state_payment }}
                    </td>
                @endif
                {{-- Fin cambiar color de fondo segun el estado del pago --}}
                {{-- Fin estado de pago --}}
                <td>
                    {{ $resource->amount }} PEN
                </td>
                <td>
                    {{ $resource->payments->sum('amount') }} PEN
                </td>
                <td>
                    <ul>
                        @foreach ($resource->schedules as $schedule)
                            <li>{{ Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_start)->format('h:i A') . ' - ' . Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_end)->format('h:i A') }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
