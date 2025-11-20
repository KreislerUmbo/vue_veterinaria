<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import { VChip, VCol, VRow } from 'vuetify/components';


const warning = ref(null);
const error_exists = ref(null);
const success = ref(null);

const event_date = ref(null);
const currentPage = ref(1);


const pet_selected = ref(null);
const historial_records = ref([]);

const list = async () => {

    if (!select_pet.value) {
        warning.value = "Necesita seleccionar una mascota para iniciar el proceso de búsqueda";
        return;
    }

    let data = {
        pet_id: select_pet.value.id,
        start_date: event_date.value ? event_date.value.split("to")[0] : null,
        end_date: event_date.value ? event_date.value.split("to")[1] : null,
    }
    const resp = await $api('/medical-records/pet?page=' + currentPage.value, {
        method: 'POST',
        body: data,
        onResponseError({ response }) {
            error_exists.value = response;
            console.log(response);
        }
    })
    warning.value = null;
    error_exists.value = null;
    console.log(resp);

    pet_selected.value = resp.pet;
    historial_records.value = resp.historial_records.data;
}


const search_medical_reord = () => {
    list();
}

const reset=()=>{
    pet_selected.value=null,
    historial_records.value=[];
    select_pet.value=[];
    event_date.value=null;
}

//codigo para la busqueda de mascotas
const loading = ref(false)
const search = ref()
const select_pet = ref(null)

const items = ref([])

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
</script>
<template>
    <div>
        <VCardText class="pa-5">
            <div class="mb-1">
                <h4 class="text-h4 text-center mb-1">
                    📅 HISTORIAL MEDICO🐶🐰🐷
                </h4>
            </div>
        </VCardText>
        <VCard title="🔍Busqueda:" class="pa-4">
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

                            </div>
                        </template>
                    </VAutocomplete>
                </VCol>

                <VAlert type="warning" class="mt-3" v-if="warning">
                    <strong>{{ warning }}</strong>
                </VAlert>
                <VAlert type="error" class="mt-3" v-if="error_exists">
                    <strong>En el servidor hubo un error al momento de guardar los datos</strong>
                </VAlert>
                <VAlert type="success" class="mt-3" v-if="success">
                    <strong>{{ success }}</strong>
                </VAlert>

                <VCol cols="4">
                    <AppDateTimePicker v-model="event_date" label="Fecha del servicio" placeholder="Select Fecha"
                        :config="{ mode: 'range' }" />

                </VCol>

                <VCol cols="4">
                    <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="search_medical_reord()">
                    </VBtn>

                    <VBtn color="secondary" prepend-icon="ri-restart-line" @click="reset()">
                    </VBtn>
                </VCol>
            </VRow>
        </VCard>
        <VRow class="my-2" v-if="pet_selected">
            <VCol cols="4">
                <VCard>
                    <VCardText class="text-center pt-12 pb-6">
                        <!-- 👉 Avatar -->
                        <VAvatar rounded="lg" :size="120" :color="'primary'" :variant="'tonal'">
                            <VImg :src="pet_selected.photo" />
                            <!--<span v-else class="text-5xl font-weight-medium">
                                {{ avatarText(props.userData.fullName) }}
                            </span>-->
                        </VAvatar>

                        <!-- 👉 User fullName -->
                        <h5 class="text-h5 mt-4">
                            {{ pet_selected.name }}
                        </h5>

                        <!-- 👉 Role chip -->
                        <VChip :color="'primary'" size="small" class="text-capitalize mt-4">
                            {{ pet_selected.breed }}
                        </VChip>
                    </VCardText>

                    <VCardText class="d-flex justify-center flex-wrap gap-6 pb-6">
                        <!-- 👉 nro de citas -->
                        <div class="d-flex align-center me-8">
                            <VAvatar :size="40" rounded color="primary" variant="tonal" class="me-4">
                                <VIcon size="24" icon="ri-list-check-3" />
                            </VAvatar>

                            <div>
                                <h5 class="text-h5">
                                    {{ pet_selected.n_appointment }}
                                </h5>
                                <span>Citas</span>
                            </div>
                        </div>

                        <!-- 👉 nro vacunas -->
                        <div class="d-flex align-center me-4">
                            <VAvatar :size="44" rounded color="primary" variant="tonal" class="me-4">
                                <VIcon size="24" icon="ri-syringe-line" />
                            </VAvatar>

                            <div>
                                <h5 class="text-h5">
                                    {{ pet_selected.n_vaccination }}
                                </h5>
                                <span> Vacunas</span>
                            </div>
                        </div>
                        <!-- 👉 Done cirugias -->
                        <div class="d-flex align-center me-4">
                            <VAvatar :size="44" rounded color="primary" variant="tonal" class="me-4">
                                <VIcon size="24" icon="ri-microscope-line" />
                            </VAvatar>

                            <div>
                                <h5 class="text-h5">
                                    {{ pet_selected.n_surgerie }}
                                </h5>
                                <span>Cirugías</span>
                            </div>
                        </div>
                    </VCardText>

                    <!-- 👉 Details -->
                    <VCardText class="pb-6">
                        <div class="text-body-2 mb-4 text-disabled">
                            Datos Generales Paciente
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-star-smile-line" size="24" />
                                <div class="font-weight-medium">
                                    Mascota:
                                </div>
                                <div>
                                    {{ pet_selected.name }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-p2p-fill" size="24" />
                                <div class="font-weight-medium">
                                    Sexo:
                                </div>
                                <div>
                                    {{ pet_selected.gender == 'M' ? 'Macho' : 'Hembra' }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-skull-2-line" size="24" />
                                <div class="font-weight-medium">
                                    Especie:
                                </div>
                                <div>
                                    {{ pet_selected.specie }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-pulse-line" size="24" />
                                <div class="font-weight-medium">
                                    Raza:
                                </div>
                                <div>
                                    {{ pet_selected.breed }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-calendar-2-fill" size="24" />
                                <div class="font-weight-medium">
                                    F. Nacimiento:
                                </div>
                                <div>
                                    {{ pet_selected.dirth_date.split('-').reverse().join('-') }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-medicine-bottle-line" size="24" />
                                <div class="font-weight-medium">
                                    Peso:
                                </div>
                                <div>
                                    {{ pet_selected.weight }} KG
                                </div>
                            </div>
                        </div>

                        <div class="text-body-2 text-disabled mt-6 mb-4">
                            RESPONSABLE/DUEÑO
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-user-line" size="24" />
                                <div class="font-weight-medium">
                                    Nombres:
                                </div>
                                <div class="text-truncate">
                                    {{ pet_selected.owner.first_name + ' ' + pet_selected.owner.last_name }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-cellphone-fill" size="24" />
                                <div class="font-weight-medium">
                                    Telefóno:
                                </div>
                                <div class="text-truncate">
                                    {{ pet_selected.owner.phone }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-passport-line" size="24" />
                                <div class="font-weight-medium">
                                    Tipo Documento:
                                </div>
                                <div class="text-truncate">
                                    {{ pet_selected.owner.type_document }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-id-card-line" size="24" />
                                <div class="font-weight-medium">
                                    Nro Documento:
                                </div>
                                <div class="text-truncate">
                                    {{ pet_selected.owner.n_document }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-building-4-line" size="24" />
                                <div class="font-weight-medium">
                                    Dirección:
                                </div>
                                <div class="text-truncate">
                                    {{ pet_selected.owner.address + ' ' + pet_selected.owner.city }}
                                </div>
                            </div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
            <VCol cols="8">
                <VCard title="Servicios Realizados en el Tiempo">
                    <VCardText>
                        <VTimeline side="end" align="start" line-inset="9" truncate-line="start" density="compact">
                            <template v-for="(historial_record, index) in historial_records" : key="index">

                                <!-- SECTION Timeline Item: Citas medicas -->
                                <VTimelineItem size="x-small" dot-color="primary" v-if="historial_record.event_type == 1">
                                    <!-- 👉 Header -->
                                    <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                        <div class="app-timeline-title">
                                            Cita Médica {{ historial_record.event_date.split('-').reverse().join('-') }}
                                            <VChip class="mx-3" v-if="historial_record.state == 1" color="warning">
                                                Pendiente</VChip>
                                            <VChip class="mx-3" v-if="historial_record.state == 2" color="error">Cancelado
                                            </VChip>
                                            <VChip class="mx-3" v-if="historial_record.state == 3" color="success">
                                                Atendido</VChip>
                                        </div>
                                        <span class="app-timeline-meta">{{ historial_record.created_at }}</span>
                                    </div>

                                    <div class="app-timeline-text mt-3">
                                        <span class="app-timeline-meta"></span>
                                        La atención inicia @{{ historial_record.hour_start }} hasta @{{
                                        historial_record.hour_end }}
                                        <br>
                                        Costo: {{ historial_record.amount }} PEN
                                        <br><br>
                                        Notas Médicas:
                                        {{ historial_record.reason }}
                                            <br>
                                        Resultado:
                                         {{ historial_record.outcome }}
                                    </div>

                                    <!-- 👉 Person -->
                                    <div class="d-flex justify-space-between align-center flex-wrap">
                                        <!-- 👉 Avatar & Personal Info -->
                                        <div class="d-flex align-center my-2">
                                            <VAvatar size="32" class="me-2"
                                                :image="historial_record.veterinarie.imagen" />
                                            <div class="d-flex flex-column">
                                                <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                    {{ historial_record.veterinarie.full_name }}
                                                </p>
                                                <span class="text-sm">{{ historial_record.veterinarie.designation
                                                    }}</span>
                                            </div>
                                        </div>                                    
                                    </div>
                                      <hr>
                                </VTimelineItem>
                                <!-- !SECTION -->
                      
                                <!-- SECTION Timeline Item: Vacunacione -->
                                <VTimelineItem size="x-small" dot-color="warning" v-if="historial_record.event_type == 2">
                                    <!-- 👉 Header -->
                                    <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                        <div class="app-timeline-title">
                                            Vacunación {{ historial_record.event_date.split('-').reverse().join('-') }}
                                            <VChip class="mx-3" color="warning" v-if="historial_record.state == 1">
                                                Pendiente</VChip>
                                            <VChip class="mx-3" color="error" v-if="historial_record.state == 2">Cancelado
                                            </VChip>
                                            <VChip class="mx-3" color="success" v-if="historial_record.state == 3">
                                                Atendido</VChip>

                                        </div>
                                        <span class="app-timeline-meta">{{ historial_record.created_at }}</span>
                                    </div>

                                    <div class="app-timeline-text mt-3">
                                        La atención inicia @{{ historial_record.hour_start }} hasta @{{
                                        historial_record.hour_end }}
                                        <br>
                                        Costo: {{ historial_record.amount }} PEN
                                        <br>
                                        Lugar de atención:
                                        {{ historial_record.outside == 0 ? 'Dentro de la veterinaria' : 'Fuera de la veterinaría' }}
                                        <br>
                                        Proxima Fecha de Vacunación:
                                        {{ historial_record.nex_due_date }}
                                                      <br><br>
                                        Notas Médicas:
                                        {{ historial_record.reason }}
                                            <br>
                                        Resultado:
                                         {{ historial_record.outcome }}
                                    </div>
                                        
                                    <!-- 👉 Person -->
                                    <div class="d-flex justify-space-between align-center flex-wrap">
                                        <!-- 👉 Avatar & Personal Info -->
                                        <div class="d-flex align-center my-2">
                                            <VAvatar size="32" class="me-2"  :image="historial_record.veterinarie.imagen" />
                                            <div class="d-flex flex-column">
                                                <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                    {{ historial_record.veterinarie.full_name }}
                                                </p>
                                                <span class="text-sm">{{ historial_record.veterinarie.designation
                                                    }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                </VTimelineItem>
                                <!-- !SECTION -->

                                <!-- SECTION Design Cirugias -->
                                <VTimelineItem size="x-small" dot-color="success" v-if="historial_record.event_type == 3">
                                    <!-- 👉 Header -->
                                    <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                        <div class="app-timeline-title">
                                            Cirugía  {{ historial_record.event_date.split('-').reverse().join('-') }}
                                            <VChip class="mx-3" v-if="historial_record.state == 1" color="warning">
                                                Pendiente</VChip>
                                            <VChip class="mx-3" v-if="historial_record.state == 2" color="error">Cancelado
                                            </VChip>
                                            <VChip class="mx-3" v-if="historial_record.state == 3" color="success">
                                                Atendido</VChip>
                                        </div>
                                        <span class="app-timeline-meta">{{ historial_record.created_at }}</span>
                                    </div>

                                    <div class="app-timeline-text mt-3">
                                     La atención inicia @{{ historial_record.hour_start }} hasta @{{
                                        historial_record.hour_end }}
                                        <br>
                                        Costo: {{ historial_record.amount }} PEN
                                        <br>
                                        Lugar:
                                       {{ historial_record.outside == 0 ? 'Dentro de la veterinaria' : 'Fuera de la veterianria' }}
                                        <br>
                                        Tipo de Cirugía:
                                        {{ historial_record.surgerie_type }}
                                        <br><br>
                                        Notas Médicas:
                                        {{ historial_record.medical_notes }}
                                            <br>
                                        Resultado:
                                         {{ historial_record.outcome }}
                                    </div>

                                    <!-- 👉 Person -->
                                    <div class="d-flex justify-space-between align-center flex-wrap">
                                        <!-- 👉 Avatar & Personal Info -->
                                        <div class="d-flex align-center my-2">
                                            <VAvatar size="32" class="me-2"  :image="historial_record.veterinarie.imagen" />
                                            <div class="d-flex flex-column">
                                                <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                    {{ historial_record.veterinarie.full_name }}
                                                </p>
                                                <span class="text-sm">{{ historial_record.veterinarie.designation
                                                    }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                </VTimelineItem>
                                <!-- !SECTION -->
                            </template>
                        </VTimeline>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>