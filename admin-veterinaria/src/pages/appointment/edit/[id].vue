<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { $api } from '@/utils/api';
import { set } from '@vueuse/core';
import { VBtn, VCard, VCardText, VCol, VRow, VSelect, VTextarea, VTextField } from 'vuetify/components';

const warning = ref(null);
const error_exists = ref(null);
const success = ref(null);
const route = useRoute('appointment-edit-id');//obtiene el id de la ruta
const router = useRouter();// para redireccionar a otra pagina


const form = ref({
    date_appointment: null,
    time: null,
    amount: 0,
    method_payment: 'Efectivo',
    amount_add: 0,
    state: 1, // estado de la cita 1: pendiente, 2: cancelado, 3: atendido
})

const method_payments = ref([
    { id: 1, name: 'Efectivo' },
    { id: 2, name: 'Yape' },
    { id: 3, name: 'Plin' },
    { id: 4, name: 'Tarjeta' },
    { id: 5, name: 'Trasferencia' },
    { id: 6, name: 'Paypal' },
])
const veternarie_time_availability = ref([]);
const segment_time_veterinaries = ref([]);
const selected_segment_times = ref([]);
const veterinarie_id = ref(null);
const reason = ref(null);
const appointment_selected = ref(null);

const error_exist = ref(false);

const filter = async () => {// funcion para filtrar la disponibilidad de los veterinarios
    try {
        if (!form.value.date_appointment) {
            warning.value = "Debe seleccionar fecha para buscar disponibilidad";
            return;
        }
        let data = {
            date_appointment: form.value.date_appointment,
            hour: form.value.time,
        }
        const resp = await $api('/appointments/filter-availability', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);

        veternarie_time_availability.value = resp.veternarie_time_availability;

    } catch (error) {
        console.log(error);
    }
}
const selectedSegmentHour = (veternarie_time, segment_time_group) => { //del boton de los horarios al hacer check
    veternarie_time.segment_times = segment_time_group.segment_times;
    selected_segment_times.value = [];//reinicia el array para que no se acumulen los horarios seleccionados
    segment_time_veterinaries.value = []; //reinicia el array para que no se acumulen los horarios seleccionados
}

const reset = () => {
    form.value.date_appointment = null;
    form.value.time = null;
    veternarie_time_availability.value = [];
    segment_time_veterinaries.value = [];
    selected_segment_times.value = [];
    form.value.amount = 0;
    form.value.method_payment = 'Efectivo';
    form.value.amount_add = 0;
}
const addSelectedSegmentTime = (veternarie_time, segment_time) => {// al hacer check en el horario
    let INDEX = selected_segment_times.value.findIndex((item => item.veterinarie_id == veternarie_time.id && item.segment_time_id == segment_time.veterinarie_schedule_hour_id));
    if (INDEX != -1) {// si ya existe el horario seleccionado
        selected_segment_times.value.splice(INDEX, 1);//elimina el horario seleccionado

    } else {// si no existe el horario seleccionado
        selected_segment_times.value.push({             // agrega el horario seleccionado 
            veterinarie_id: veternarie_time.id,         // asigna el id del veterinario
            segment_time_id: segment_time.veterinarie_schedule_hour_id, // asigna el id del segmento de tiempo
        });
    }
    veterinarie_id.value = veternarie_time.id;          // asigna el id del veterinario

    //filtra los horarios seleccionados para que solo queden los del veterinario seleccionado
    selected_segment_times.value = selected_segment_times.value.filter((item) => {
        return item.veterinarie_id = veternarie_time.id;
    });

    segment_time_veterinaries.value = segment_time_veterinaries.value.filter((item) => {
        return item.indexOf(veternarie_time.id + "-") != -1;
    });

}
const limpiarCampos = () => {
    form.value.date_appointment = null;
    form.value.time = null;
    veternarie_time_availability.value = [];
    segment_time_veterinaries.value = [];
    selected_segment_times.value = [];
}

// funcion para actualizar la cita medica
const update = async () => {
    try {
        success.value = null;
        warning.value = null;
        error_exists.value = null;

        if (form.value.date_appointment) {
            if (selected_segment_times.value.length == 0) {
                warning.value = "si cambia la fecha de la cita, debe buscar y seleccionar al menos un horario de un veterinario";
                return;
            }
        }

        if (!select_pet.value) {
            warning.value = "Debe seleccionar una mascota";
            return;
        }
        /*  if (segment_time_veterinaries.value.length == 0) {
              warning.value = "Debe seleccionar al menos un horario de un veterinario";
              return;
          }*/
        if (parseInt(form.value.amount <= 0)) {
            warning.value = "Debe ingresar un costo total del servicio";
            return;
        }

        let data = {                            // datos para enviar al backend
            veterinarie_id: veterinarie_id.value,
            pet_id: select_pet.value.id,
            reason: reason.value,
            //date_appointment: form.value.date_appointment,
            //time: form.value.time,
            amount: form.value.amount,
            // state_pay: STATE_PAY,
            // method_payment: form.value.method_payment,
            // adelanto: form.value.amount_add,
            selected_segment_times: selected_segment_times.value,
            state: form.value.state,
            // segment_times: segment_time_veterinaries.value
        }
        if (form.value.date_appointment) {
            data.date_appointment = form.value.date_appointment;
        }

        const resp = await $api('/appointments/' + route.params.id, {  // envia los datos al backend para guardar la cita
            method: 'PATCH',
            body: data,
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);
        if (resp.message == 403) {
            warning.value = resp.message_text;
            return;
        } 
        else {
            success.value = "Se actualizó correctamente la cita médica";
            show(); // muestra los datos actualizados de la cita medica
        }

        setTimeout(() => {
            success.value = null;
            warning.value = null;
            error_exists.value = null;
            limpiarCampos();
        }, 2000);
        //router.push({ name: 'appointments' });
    } catch (error) {
        console.log(error);
    }
}

//codigo para la busqueda de mascotas
const loading = ref(false)// para el loading del autocomplete
const search = ref()
const select_pet = ref(null)// para obtener el objeto seleccionado del autocomplete


const items = ref([])// para almacenar los resultados de la busqueda

const querySelections = async (query) => {                      //funcion para buscar las mascotas
    loading.value = true
    // Simulated ajax query o http 
    setTimeout(async () => {
        //  items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase())) // toLowerCase() Convierte todo el texto (string) a letras minúsculas.
        const resp = await $api("/appointments/search-pets/" + query, {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);
        items.value = resp.pets;
        loading.value = false
    }, 500)
}

watch(search, query => {// vigila el cambio en el input de busqueda
    if (query && query.length > 2) {// busca si el input tiene mas de 2 caracteres
        querySelections(query)
    } else {
        items.value = [];
    }
    //query && query !== select.value && querySelections(query)
})
//fin de la busqueda de macota


const show = async () => {// funcion spara mostrar los datos de la cita medica
    try {
        const resp = await $api("/appointments/" + route.params.id, {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }
        })
        console.log(resp);
        appointment_selected.value = resp.appointment;// almacena los datos de la cita medica en la variable appointment_selected
        reason.value = appointment_selected.value.reason;
        form.value.amount = Number(appointment_selected.value.amount);
        form.value.state = appointment_selected.value.state;
        select_pet.value = appointment_selected.value.pet;// asigna la mascota seleccionada. NO olvidar que appointment_selected.value.pet; viene del AppointmentResource
        veterinarie_id.value = appointment_selected.value.veterinarie_id;// el veterinarie_id viene del AppointmentResource y lo asigna a la variable veterinarie_id
    } catch (error) {
        console.log(error);
    }
}

onMounted(() => {
    show();
});


definePage({
    meta: {
        permissions: ['edit_appointment'],
    },
});

</script>

<template>
    <div>
        <VCardText class="pa-5">
            <div class="mb-1">
                <h4 class="text-h4 text-center mb-1">
                    📅 MODIFICAR CITAS MEDICAS : #{{ route.params.id }}
                </h4>
            </div>
        </VCardText>
        <VCard title="🔍Busqueda:" class="pa-4">
            <VRow>
                <VCol cols="4">
                    <AppDateTimePicker v-model="form.date_appointment" label="Fecha" placeholder="Select Fecha" :config="{
                        minDate: 'today', disable: [
                            (date) => {
                                // Deshabilita sábados (6) y domingos (0)
                                return date.getDay() === 0 || date.getDay() === 6;
                            },
                        ]
                    }" />

                </VCol>
                <VCol cols="4">
                    <AppDateTimePicker v-model="form.time" label="Hora de la Citas" placeholder="Select time"
                        :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }" />
                </VCol>
                <VCol cols="4">
                    <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="filter()">
                    </VBtn>

                    <VBtn color="secondary" prepend-icon="ri-restart-line" @click="reset()">
                    </VBtn>
                </VCol>
            </VRow>
        </VCard>
        <VAlert type="warning" class="mt-3" v-if="warning">
            <strong>{{ warning }}</strong>
        </VAlert>
        <VAlert type="error" class="mt-3" v-if="error_exist">
            <strong>{{ message_text }}</strong>
        </VAlert>
        <VAlert type="success" class="mt-3" v-if="success">
            <strong>{{ success }}</strong>
        </VAlert>
        <VCard title="📅 Disponibilidad:" class="pa-4 mt-4">
            <VRow>
                <VCol cols="8">
                    <VTable>
                        <thead>
                            <tr>
                                <th class="text-uppercase">
                                    Veterinarios
                                </th>
                                <th class="text-uppercase">
                                    Tiempos Activos
                                </th>
                                <th class="text-uppercase">
                                    Segmento Tiempos
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            {{ segment_time_veterinaries }}
                            <template v-for="(veternarie_time, index) in veternarie_time_availability"
                                :key="index"><!--v-for para recorrer los veterinarios con disponibilidad-->
                                <tr>
                                    <td>
                                        {{ veternarie_time.full_name }}
                                    </td>
                                    <td>
                                        <ul><!-- v-for para recorrer los grupos de horarios-->
                                            <li v-for="(segment_time_group, index) in veternarie_time.segment_time_groups"
                                                :key="index" style="list-style: none;">
                                                <VBtn color="success" class="mx-1" prepend-icon="ri-file-add-line"
                                                    variant="text"
                                                    @click="selectedSegmentHour(veternarie_time, segment_time_group)">
                                                </VBtn>
                                                {{ segment_time_group.hour_format }}({{
                                                    segment_time_group.count_availability }})
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul><!-- v-for para recorrer los horarios-->
                                            <li v-for="(segment_time, index) in veternarie_time.segment_times"
                                                :key="index" style="list-style: none;">
                                                <!-- al hacer click en el checkbox -->
                                                <VCheckbox v-if="!segment_time.check"
                                                    @click="addSelectedSegmentTime(veternarie_time, segment_time)"
                                                    v-model="segment_time_veterinaries"
                                                    :label="segment_time.schedule_hour.hour_star_format + ' - ' + segment_time.schedule_hour.hour_end_format"
                                                    :value="veternarie_time.id + '-' + segment_time.veterinarie_schedule_hour_id" />
                                                <!-- v-if="segment_time.check" es si el horario ya esta seleccionado aparece sin check y de negrita -->
                                                <label for="" style="font-weight: bold;" v-if="segment_time.check">{{
                                                    segment_time.schedule_hour.hour_star_format + ' - ' +
                                                    segment_time.schedule_hour.hour_end_format }}
                                                </label>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </VTable>
                </VCol>
                <VCol cols="4">
                    <VTextarea v-model="reason" label="Motivo de la Cita"
                        placeholder="Ingrese el motivo de la cita medica" rows="7" />
                    <!--density="compact"-->
                </VCol>
            </VRow>
        </VCard>

        <VCard title="🧭 Horario de la Cita:" v-if="appointment_selected" class="pa-4 mt-4">
            <VRow>
                <VCol cols="10">
                    <VTable>
                        <thead>
                            <tr>
                                <th>
                                    <!-- Veterinarios -->
                                    <span class="label-title">Estado de la cita:</span>
                                </th>
                                <th class="text-uppercase">
                                    Veterinarios
                                </th>
                                <th>
                                    <!-- Horarios de Atención -->
                                    <span class="label-title">Fecha de la Cita:</span>
                                </th>

                                <th class="text-uppercase">
                                    Horarios de Atención
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <VSelect :items="[
                                        {
                                            name: 'Pendiente',
                                            id: 1
                                        },
                                        {
                                            name: 'Cancelado',
                                            id: 2
                                        },
                                        {
                                            name: 'Atendido',
                                            id: 3
                                        }
                                    ]" v-model="form.state" item-title="name" item-value="id" :disabled="appointment_selected.state==2 || appointment_selected.state==3 ? true:false"
                                        placeholder="Select Estado Cita" eager />
                                </td>
                                <td>
                                    {{ appointment_selected.veterinarie.full_name }}
                                </td>
                                <td>
                                    {{ appointment_selected.date_appointment }}
                                </td>
                                <td>
                                    <ul>
                                        <template v-for="(schedule, index) in appointment_selected.schedules"
                                            :key="index"><!--v-for para recorrer los veterinarios con disponibilidad-->
                                            <li>
                                                <label for="" style="font-weight: bold;">
                                                    {{ schedule.schedule_hour.hour_start_format + ' - '
                                                        + schedule.schedule_hour.hour_end_format }}
                                                </label>
                                            </li>
                                        </template>
                                    </ul>
                                </td>
                            </tr>

                        </tbody>
                    </VTable>
                </VCol>
            </VRow>

        </VCard>

        <VCard title="🐶 Paciente:" class="pa-4 mt-2">
            <VRow>
                <VCol cols="4">
                    <VAutocomplete v-model="select_pet" v-model:search="search" :loading="loading" :items="items"
                        item-title="name" item-value="id" return-object placeholder="Ingrese Inf. de la mascota"
                        label="Quien es la mascotita?" variant="underlined" :menu-props="{ maxHeight: '200px' }">
                        <template #no-data>
                            <div class="text-center pa-4" v-if="search && search.length > 0">
                                <VListItem>
                                    <VListItemTitle>
                                        No se encontró la mascota
                                    </VListItemTitle>
                                </VListItem>
                                <VListItem>
                                    <VBtn color="primary" block @click="dialog = true">
                                        Registrar nueva mascota
                                    </VBtn>
                                </VListItem>
                            </div>
                        </template>
                    </VAutocomplete>
                </VCol>
                <VCol cols="2" v-if="select_pet">

                    <label for="">
                        <span class="label-title"> Especie:</span>
                        {{ select_pet.specie }}
                    </label>
                    <br>
                    <label for="">
                        <span class="label-title">Raza:</span> {{ select_pet.breed }}
                    </label>
                </VCol>
                <VCol cols="6" v-if="select_pet">
                    <label for="">
                        <span class="label-title">Dueño/Responsable de la Mascota:</span>
                        {{ select_pet.owner.first_name + ' ' + select_pet.owner.last_name }}</label>
                    <br>
                    <label for="">
                        <span class="label-title">Telefono:</span> {{ select_pet.owner.phone }}
                    </label>
                    <br>
                    <label for="">
                        <span class="label-title">Nro Documento:</span>
                        {{ select_pet.owner.n_document }}
                    </label>
                </VCol>
            </VRow>
        </VCard>

        <VCard title="💰 Pagos:" class="pa-4 mt-2">
            <VRow>
                <VCol cols="4">
                    <VTextField v-model.number="form.amount" label="Consto total Servicio" prefix="S/" type="number"
                        placeholder="Ingrese el valor total del servicio" /><!--density="compact"-->
                </VCol>
            </VRow>
        </VCard>
        <VCardText class="pa-5 text-center mt-2 py-0">
            <VBtn color="primary" class="mx-1" prepend-icon="ri-save-2-line" @click="update()">
                Editar Cita
            </VBtn>
            <VBtn color="error" class="mx-1" prepend-icon="ri-close-line"
                @click="router.push({ name: 'appointment-list' })">
                Listado
            </VBtn>
        </VCardText>

    </div>
</template>
<style>
.label-title {
    background-color: #f0f0f0;
    font-weight: bold;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>