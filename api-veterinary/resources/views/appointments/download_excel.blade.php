<table style="border: 1px">
    <thead>
        <tr>
            <th>#</th>
            <th width="40">Mascota</th>
            <th width="20">Especie</th>
            <th width="40">Veterinario</th>
            <th width="20">Fecha Cita</th>
            <th width="20">Estado Cita</th>
            <th width="20">Estado Pago</th>
            <th width="40">Horario</th>

        </tr>
    </thead>
    <tbody>

        @foreach ($appointments123 as $key => $appointment)
        {{ count($appointments123) }}
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $appointment->pet->name }}</td>
                <td>{{ $appointment->pet->specie }}</td>
                <td>{{ $appointment->veterinarie->name . ' ' . $appointment->veterinarie->surname }}</td>
                <td>{{ Carbon\Carbon::parse($appointment->date_appointment)->format('d/m/Y') }}</td>
                @php
                    $state_appointment = '';
                    switch ($appointment->state) {
                        case 1:
                            $state_appointment = 'Pendiente';
                            break;
                        case 2:
                            $state_appointment = 'Cancelada';
                            break;
                        case 3:
                            $state_appointment = 'Atendida';
                            break;
                        default:
                            # code...
                            break;
                    }
                @endphp
                {{-- Cambiar color de fondo segun el estado de la cita --}}
                @if ($appointment->state == 1)                    
                    <td style="background: #fbff18;">
                        {{ $state_appointment }}
                    </td>                  
                @endif

                @if ($appointment->state == 2)
                    <td style="background: #f50808;">
                        {{ $state_appointment }}
                    </td>                   
                @endif

                @if ($appointment->state == 3)                  
                    <td style="background: #0efa06;">
                        {{ $state_appointment }}
                    </td>                 
                @endif
                {{-- Fin cambiar color de fondo segun el estado de la cita --}}
                {{-- Estado de pago --}}

                @php
                    $state_payment = '';
                    switch ($appointment->state_pay) {
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
                @if ($appointment->state_pay == 1)                
                    <td style="background: #f3f167;">
                        {{ $state_payment }}
                    </td>                 
                @endif

                @if ($appointment->state_pay == 2)        
                    <td style="background: #fa8deb;">
                        {{ $state_payment }}
                    </td>                  
                @endif

                @if ($appointment->state_pay == 3)                  
                    <td style="background: #8cc7f8;">
                        {{ $state_payment }}
                    </td>                  
                @endif
                {{-- Fin cambiar color de fondo segun el estado del pago --}}
                {{-- Fin estado de pago --}}

                <td>
                    <ul>
                        @foreach ($appointment->schedules as $schedule)
                            <li>{{ Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_start)->format('h:i A') . ' - ' . Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_end)->format('h:i A') }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
