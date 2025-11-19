<table style="border: 1px">
    <thead>
        <tr>
            <th>#</th>
            <th width="40">Mascota</th>
            <th width="20">Especie</th>
            <th width="40">Veterinario</th>
            <th width="20">Fecha Cirugia</th>
            <th width="20">Estado cirugía</th>
            <th width="20">Estado Pago</th>
            <th width="40">Horario</th>

        </tr>
    </thead>
    <tbody>

        @foreach ($surgeries_data as $key => $surgerie)
        {{ count($surgeries_data) }}
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $surgerie->pet->name }}</td>
                <td>{{ $surgerie->pet->specie }}</td>
                <td>{{ $surgerie->veterinarie->name . ' ' . $surgerie->veterinarie->surname }}</td>
                <td>{{ Carbon\Carbon::parse($surgerie->vaccionation_date)->format('d/m/Y') }}</td>
                @php
                    $state_surgerie = '';
                    switch ($surgerie->state) {
                        case 1:
                            $state_surgerie = 'Pendiente';
                            break;
                        case 2:
                            $state_surgerie = 'Cancelada';
                            break;
                        case 3:
                            $state_surgerie = 'Atendida';
                            break;
                        default:
                            # code...
                            break;
                    }
                @endphp
                {{-- Cambiar color de fondo segun el estado de la cita --}}
                @if ($surgerie->state == 1)                    
                    <td style="background: #fbff18;">
                        {{ $state_surgerie }}
                    </td>                  
                @endif

                @if ($surgerie->state == 2)
                    <td style="background: #f50808;">
                        {{ $state_surgerie }}
                    </td>                   
                @endif

                @if ($surgerie->state == 3)                  
                    <td style="background: #0efa06;">
                        {{ $state_surgerie }}
                    </td>                 
                @endif
                {{-- Fin cambiar color de fondo segun el estado de la cita --}}
                {{-- Estado de pago --}}

                @php
                    $state_payment = '';
                    switch ($surgerie->state_pay) {
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
                @if ($surgerie->state_pay == 1)                
                    <td style="background: #f3f167;">
                        {{ $state_payment }}
                    </td>                 
                @endif

                @if ($surgerie->state_pay == 2)        
                    <td style="background: #fa8deb;">
                        {{ $state_payment }}
                    </td>                  
                @endif

                @if ($surgerie->state_pay == 3)                  
                    <td style="background: #8cc7f8;">
                        {{ $state_payment }}
                    </td>                  
                @endif
                {{-- Fin cambiar color de fondo segun el estado del pago --}}
                {{-- Fin estado de pago --}}

                <td>
                    <ul>
                        @foreach ($surgerie->schedules as $schedule)
                            <li>{{ Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_start)->format('h:i A') . ' - ' . Carbon\Carbon::parse(date('Y-m-d') . ' ' . $schedule->schedule_hour->hour_end)->format('h:i A') }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
