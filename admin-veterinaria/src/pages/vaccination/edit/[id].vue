<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { $api } from '@/utils/api';
import { onMounted } from 'vue';
import { VBtn, VCard, VCardText, VCol, VRadio, VRadioGroup, VRow, VSelect, VTextarea, VTextField } from 'vuetify/components';

const route = useRoute('vaccination-edit-id'); // obtiene el id de la ruta
const warning = ref(null);
const error_exists = ref(null);
const success = ref(null);
const router = useRouter();


const form = ref({
    vaccionation_date: null,
    time: null,
    amount: 0,
    method_payment: 'Efectivo',
    amount_add: 0,
    state: null,
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
const segment_time_hour_veterinaries = ref([]);// para los checkbox de los grupos de horarios
const veterinarie_id = ref(null);
const reason = ref(null);
const vaccine_names = ref(null);
const nex_due_date = ref(null);
const outside = ref('0');// si la vacuna se aplica dentro o fuera de la clinica, por defecto dentro de la clinica con valor 0
const error_exist = ref(false);
const vaccination_selected = ref(null); // para almacenar los datos de la vacunacion seleccionada
const state = ref(null); // para almacenar el estado de la vacunacion seleccionada

//funcion para filtrar la disponibilidad de los veterinarios
const filter = async () => {
    try {
        if (!form.value.vaccionation_date) {
            warning.value = "Debe seleccionar fecha para buscar disponibilidad";
            return;
        }
        let data = {
            vaccionation_date: form.value.vaccionation_date,
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

        veternarie_time_availability.value = resp.veternarie_time_availability;// asigna la disponibilidad de los veterinarios que viene del backend controlador appointment  funcion filter retorna  filterAvailability

    } catch (error) {
        console.log(error);
    }
}

//funcion para seleccionar los horarios de un veterinario
const selectedSegmentHour = (veternarie_time, segment_time_group) => { //del boton de los horarios al hacer check

    veternarie_time.segment_times = segment_time_group.segment_times;// asigna los horarios del grupo de horarios seleccionado al veterinario
    //  selected_segment_times.value = [];//reinicia el array para que no se acumulen los horarios seleccionados
    // segment_time_veterinaries.value = []; //reinicia el array para que no se acumulen los horarios seleccionados

    veterinarie_id.value = veternarie_time.id;          // asigna el id del veterinario
    //filtra los horarios seleccionados para que solo queden los del veterinario seleccionado
    selected_segment_times.value = selected_segment_times.value.filter((item) => {
        return item.veterinarie_id = veternarie_time.id;
    });
    segment_time_veterinaries.value = segment_time_veterinaries.value.filter((item) => {
        return item.indexOf(veternarie_time.id + "-") != -1;
    });
    segment_time_hour_veterinaries.value = segment_time_hour_veterinaries.value.filter((item) => {
        return item.indexOf(veternarie_time.id + "-") != -1;
    });
}

//funcion para reiniciar los filtros 
const reset = () => {
    form.value.vaccionation_date = null;
    form.value.time = null;
    veternarie_time_availability.value = [];
    segment_time_veterinaries.value = [];
    selected_segment_times.value = [];
    error_exist.value = false;
    warning.value = null;
    segment_time_hour_veterinaries.value = [];
   // hour_start_format = null;
   // hour_end_format = null;
    //segment_time_hour_veterinaries.value = [];
    //schedule_for_hour.value = [];
}

//funcion para agregar o quitar los horarios seleccionados
const addSelectedSegmentTime = (veternarie_time, segment_time) => {
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

const addSelectedSegmentTimeHour = (veternarie_time, segment_time_group) => {
    console.log(veternarie_time);
    segment_time_group.segment_times.forEach((segment_time) => {
        let INDEX = selected_segment_times.value.findIndex((item => item.veterinarie_id == veternarie_time.id && item.segment_time_id == segment_time.veterinarie_schedule_hour_id));
        if (INDEX != -1) {// si ya existe el horario seleccionado
            selected_segment_times.value.splice(INDEX, 1);//elimina el horario seleccionado
            segment_time_veterinaries.value.splice(INDEX, 1); //elimina el horario seleccionado cuando se descheckea el checkbox
        } else {// si no existe el horario seleccionado

            if (!segment_time.check) {// si el horario ya esta seleccionado no lo agrega de nuevo
                selected_segment_times.value.push({             // agrega el horario seleccionado 
                    veterinarie_id: veternarie_time.id,         // asigna el id del veterinario
                    segment_time_id: segment_time.veterinarie_schedule_hour_id, // asigna el id del segmento de tiempo
                });
                segment_time_veterinaries.value.push(veternarie_time.id + '-' + segment_time.veterinarie_schedule_hour_id);
            }
        }
        veterinarie_id.value = veternarie_time.id;          // asigna el id del veterinario
        //filtra los horarios seleccionados para que solo queden los del veterinario seleccionado
        selected_segment_times.value = selected_segment_times.value.filter((item) => {
            return item.veterinarie_id = veternarie_time.id;
        });
        segment_time_veterinaries.value = segment_time_veterinaries.value.filter((item) => {
            return item.indexOf(veternarie_time.id + "-") != -1;
        });
        segment_time_hour_veterinaries.value = segment_time_hour_veterinaries.value.filter((item) => {
            return item.indexOf(veternarie_time.id + "-") != -1;
        });
    });

}





const update = async () => {
    try {
        warning.value = null;
        success.value = null;
        error_exists.value = null;

        if (!reason.value) {
            warning.value = "Debe ingresar el motivo de la cita";
            return;
        }

        if (!select_pet.value) {
            warning.value = "Debe seleccionar una mascota";
            return;
        }
        if (!vaccine_names.value) {
            warning.value = "Debe ingresar el nombre de la vacuna";
            return;
        }
        if (!nex_due_date.value) {
            warning.value = "Debe ingresar la fecha de la próxima vacuna";
            return;
        }

        if (parseInt(form.value.amount <= 0)) {
            warning.value = "Debe ingresar un costo total del servicio";
            return;
        }

        let data = {                            // datos para enviar al backend
            veterinarie_id: veterinarie_id.value,
            pet_id: select_pet.value.id,
            reason: reason.value,
            vaccionation_date: form.value.vaccionation_date,
            amount: form.value.amount,
            method_payment: form.value.method_payment,
            selected_segment_times: selected_segment_times.value,
            vaccine_names: vaccine_names.value,
            nex_due_date: nex_due_date.value,
            outside: outside.value,
            state: form.value.state,
        }

        const resp = await $api('/vaccinations/' + route.params.id, {  /// usa el id de la ruta para editar la vacunacion
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
        } else {

            success.value = "Se editó correctamente la cita de vacunación";
            reset(); // reinicia los filtros
            show(); // llama a la funcion show para actualizar los datos de la vacunacion

        }


    } catch (error) {
        console.log(error);
    }
}

//codigo para la busqueda de mascotas
const loading = ref(false)
const search = ref()
const select_pet = ref(null)


const items = ref([])

// FUNCION PARA BUSCAR LAS MASCOTAS
const querySelections = async (query) => {
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

const show = async () => {
    try {
        const resp = await $api('/vaccinations/' + route.params.id, {  /// usa el id de la ruta para obtener los datos de la vacunacion
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exists.value = response._data.error;
            }
        })
        console.log(resp);
        // asigna los datos obtenidos del backend a las variables
        vaccination_selected.value = resp.vaccionation;
        veterinarie_id.value = vaccination_selected.value.veterinarie_id;
       // form.value.vaccionation_date = vaccination_selected.value.vaccionation_date;
        form.value.amount = vaccination_selected.value.amount;
        reason.value = vaccination_selected.value.reason;
        vaccine_names.value = vaccination_selected.value.vaccine_names;
        nex_due_date.value = vaccination_selected.value.nex_due_date;
        outside.value = vaccination_selected.value.outside.toString();
        select_pet.value = vaccination_selected.value.pet;
      //  state.value = vaccination_selected.value.state;// asigna el estado de la vacunacion seleccionada
        form.value.state = vaccination_selected.value.state;// asigna el estado de la vacunacion al formulario en horario de atencion


    } catch (error) {
        console.log(error);
    }
}

onMounted(() => {
    show(); // llama a la funcion show al montar el componente
})


// DEFINE LOS PERMISOS DE LA PAGINA
definePage({
    meta: {
        permissions: ['edit_vaccionation'],
    },
});
</script>

<template>
    <div>
        <VCardText class="pa-5">
            <div class="mb-1">
                <h4 class="text-h4 text-center mb-1">
                    📅 EDITAR DATOS DE LA VACUNACIÓN {{ route.params.id }} 🐎🐄🐖🦃
                </h4>
            </div>
        </VCardText>
        <VCard title="🔍Busqueda:" class="pa-4">
            <VRow>
                <VCol cols="4">
                    <AppDateTimePicker v-model="form.vaccionation_date" label="Fecha de la Vacuna"
                        placeholder="Select Fecha" :config="{
                            minDate: 'today', disable: [
                                (date) => {
                                    // Deshabilita sábados (6) y domingos (0)
                                    return date.getDay() === 0 || date.getDay() === 6;
                                },
                            ]
                        }" />

                </VCol>
                <VCol cols="4">
                    <AppDateTimePicker v-model="form.time" label="Hora de la Vacuna" placeholder="Select time"
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
            <strong>{{ error_exists }}</strong>
        </VAlert>
        <VAlert type="success" class="mt-3" v-if="success">
            <strong>{{ success }}</strong>
        </VAlert>
        <VCard title="📅 Disponibilidad:" class="pa-4 mt-4">
            <VRow>
                {{ segment_time_veterinaries }}
                <VCol cols="12">
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
                            <template v-for="(veternarie_time, index) in veternarie_time_availability"
                                :key="index"><!--v-for para recorrer los veterinarios con disponibilidad-->
                                <tr>
                                    <td>
                                        {{ veternarie_time.full_name }}
                                    </td>
                                    <td>
                                        <ul><!-- v-for para recorrer los grupos de horarios-->
                                            <li v-for="(segment_time_group, index) in veternarie_time.segment_time_groups"
                                                :key="index"
                                                style="list-style: none; display: flex; align-items: center;">
                                                <VCheckbox
                                                    @click="addSelectedSegmentTimeHour(veternarie_time, segment_time_group)"
                                                    v-model="segment_time_hour_veterinaries"
                                                    :value="veternarie_time.id + '-' + segment_time_group.hour_format"
                                                    v-if="segment_time_group.count_availability > 0" />

                                                <VBtn color="success" class="mx-1" prepend-icon="ri-file-add-line"
                                                    variant="text"
                                                    @click="selectedSegmentHour(veternarie_time, segment_time_group)">
                                                </VBtn>
                                                {{ segment_time_group.hour_format }}({{
                                                    segment_time_group.count_availability
                                                }})
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
            </VRow>
        </VCard>
        <VCard title="🧭 Horario de Atencion:" v-if="vaccination_selected" class="pa-4 mt-4">
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
                                    ]" v-model="form.state" item-title="name" item-value="id"
                                        :disabled="vaccination_selected.state == 2 || vaccination_selected.state == 3 ? true : false"
                                        placeholder="Select Estado Vacuna" eager />
                                </td>
                                <td>
                                    {{ vaccination_selected.veterinarie.full_name }}
                                </td>
                                <td>
                                    {{ vaccination_selected.vaccionation_date }}
                                </td>
                                <td>
                                    <ul>
                                        <template v-for="(for_hour, index) in vaccination_selected.schedule_for_hour"
                                            :key="index">
                                            <template v-if="!for_hour.is_complete">
                                                <li v-for="(schedule, index2) in for_hour.segments_time" :key="index2">
                                                    <label for="" style="font-weight: bold; color: red;">
                                                        {{ schedule.schedule_hour.hour_start_format + ' - '
                                                            + schedule.schedule_hour.hour_end_format
                                                        }}
                                                    </label>
                                                </li>
                                            </template>
                                            <li v-else>
                                                <label for="" style="font-weight: bold;">
                                                    {{ for_hour.hour_format  }}
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
                                    <VBtn color="primary" prepend-icon="ri-add-line"
                                        @click="router.push({ name: 'pet-add' })">
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
            <VRow>
                <VCol cols="5">
                    <VTextarea v-model="reason" label="Motivo de la Vacuna" placeholder="Ingrese el motivo de la vacuna"
                        rows="3" />
                    <!--density="compact"-->
                </VCol>
                <VCol cols="5">
                    <VTextarea v-model="vaccine_names" label="Nombre de la Vacuna:"
                        placeholder="Ingrese nombre de la vacuna" rows="3" />
                    <!--density="compact"-->
                </VCol>
                <VCol cols="2">
                    <AppDateTimePicker v-model="nex_due_date" label="Fecha de la próxima vacuna:"
                        placeholder="Select Fecha" :config="{
                            minDate: 'today', disable: [
                                (date) => {
                                    // Deshabilita sábados (6) y domingos (0)
                                    return date.getDay() === 0 || date.getDay() === 6;
                                },
                            ]
                        }" />
                </VCol>
                <VCol cols="12">
                    <VRadioGroup v-model="outside" inline>
                        <VRadio label="¿La vacuna se aplicará dentro de la clínica?" value="0" />
                        <VRadio label="¿La vacuna se aplicará fuera de la clínica?" value="1" />
                    </VRadioGroup>
                </VCol>
            </VRow>
        </VCard>

        <VCard title="💰 Costos y Pagos:" class="pa-4 mt-2">
            <VRow>
                <VCol cols="4">
                    <VTextField v-model="form.amount" label="Costo Total de la Vacuna" prefix="S/" type="number"
                        placeholder="Ingrese el valor total del servicio" /><!--density="compact"-->
                </VCol>
            </VRow>
        </VCard>
        <VCardText class="pa-5 text-center mt-2 py-0">
            <VBtn color="primary" class="mx-1" prepend-icon="ri-save-2-line" @click="update()">
                Editar Vacunación
            </VBtn>
            <VBtn color="error" class="mx-1" prepend-icon="ri-close-line"
                @click="router.push({ name: 'vaccination-list' })">
                Listado Vacunación
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