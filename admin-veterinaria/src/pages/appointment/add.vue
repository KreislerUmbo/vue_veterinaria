<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { $api } from '@/utils/api';
import { VBtn, VCard, VRow, VSelect, VTextField } from 'vuetify/components';

const warning = ref(null);
const error_exists = ref(null);
const success = ref(null);
const router = useRouter();


const form = ref({
    date_appointment: null,
    time: null,
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
const filter = async () => {
    try {
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
const selectedSegmentHour=(veternarie_time, segment_time_group) => {
    veternarie_time.segment_times=segment_time_group.segment_times;
}

const reset = () => {
    form.value = {
        date_appointment: null,
        time: null,
    }
}
//codigo para la busqueda de mascotas
const loading = ref(false)
const search = ref()
const select = ref(null)

const states = [
    'Alabama',
    'Alaska',
    'American Samoa',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'District of Columbia',
    'Federated States of Micronesia',
    'Florida',
    'Georgia',
    'Guam',
    'Hawaii',
    'Idaho',
    'Illinois',
    'Indiana',
    'Iowa',
    'Kansas',
    'Kentucky',
    'Louisiana',
    'Maine',
    'Marshall Islands',
    'Maryland',
    'Massachusetts',
    'Michigan',
    'Minnesota',
    'Mississippi',
    'Missouri',
    'Montana',
    'Nebraska',
    'Nevada',
    'New Hampshire',
    'New Jersey',
    'New Mexico',
    'New York',
    'North Carolina',
    'North Dakota',
    'Northern Mariana Islands',
    'Ohio',
    'Oklahoma',
    'Oregon',
    'Palau',
    'Pennsylvania',
    'Puerto Rico',
    'Rhode Island',
    'South Carolina',
    'South Dakota',
    'Tennessee',
    'Texas',
    'Utah',
    'Vermont',
    'Virgin Island',
    'Virginia',
    'Washington',
    'West Virginia',
    'Wisconsin',
    'Wyoming',
]

const items = ref(states)

const querySelections = (query) => {
    loading.value = true

    // Simulated ajax query
    setTimeout(() => {
        items.value = states.filter(state => (state || '').toLowerCase().includes((query || '').toLowerCase()))
        loading.value = false
    }, 500)
}

watch(search, query => {
    query && query !== select.value && querySelections(query)
})
//fin de la busqueda de macota
</script>

<template>
    <div>
        <VCardText class="pa-5">
            <div class="mb-1">
                <h4 class="text-h4 text-center mb-1">
                    AGREGAR CITAS MEDICAS
                </h4>
            </div>
        </VCardText>
        <VCard title="🔍Busqueda:" class="pa-4">
            <VRow>
                <VCol cols="4">
                    <AppDateTimePicker v-model="form.date_appointment" label="Fecha" placeholder="Select Fecha" />

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
                            <template v-for="(veternarie_time, index) in veternarie_time_availability" :key="index">
                                <tr>
                                    <td>
                                       {{ veternarie_time.full_name }}
                                    </td>
                                    <td>
                                        <ul>
                                            <li v-for="(segment_time_group,index) in veternarie_time.segment_time_groups"
                                                :key="index" style="list-style: none;">
                                                <VBtn color="success" class="mx-1" prepend-icon="ri-file-add-line"
                                                    variant="text"
                                                    @click="selectedSegmentHour(veternarie_time, segment_time_group)">
                                                </VBtn>
                                                {{ segment_time_group.hour_format }}({{ segment_time_group.segment_times.length }})
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            <li v-for="(segment_time,index) in veternarie_time.segment_times" :key="index" style="list-style: none;">
                                                <VCheckbox :label="segment_time.schedule_hour.hour_star_format+' '+segment_time.schedule_hour.hour_end_format" />
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

        <VCard title="🐶 Paciente:" class="pa-4 mt-2">
            <VRow>
                <VCol cols="4">
                    <VAutocomplete v-model="select" v-model:search="search" :loading="loading" :items="items"
                        placeholder="Ingrese Inf. de la mascota" label="Quien es la mascotita?" variant="underlined"
                        :menu-props="{ maxHeight: '200px' }" />
                </VCol>
                <VCol cols="3">
                    <label for="">Especie: Perro</label>
                    <label for="">Raza: Pitbull</label>
                </VCol>
                <VCol cols="3">
                    <label for="">Nombre y Appellidos del Responsable</label>
                    <label for="">Telefono:900 9000 900</label>
                    <label for="">Nro Documento: 44359826</label>
                </VCol>
            </VRow>
        </VCard>

        <VCard title="💰 Pagos:" class="pa-4 mt-2">
            <VRow>
                <VCol cols="4">
                    <VTextField label="Consto total Servicio" prefix="S/" type="number"
                        placeholder="Ingrese el valor total del servicio" /><!--density="compact"-->
                </VCol>
            </VRow>
            <VRow>
                <VCol cols="4">
                    <VSelect :items="method_payments" label="Metodo de Pago" item-title="name" item-value="id"
                        placeholder="Seleccione Metodo de Pago" />
                </VCol>

                <VCol cols="4">
                    <VTextField label="Adelanto de pago" type="number" placeholder="Ingrese Monto ejm: 100" />
                </VCol>
            </VRow>
        </VCard>

    </div>
</template>