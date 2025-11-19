<script setup>
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import { VChip, VCol, VRow } from 'vuetify/components';


const event_date = ref(null);

const search_medical_reord = ref(

)

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
                                <VListItem>
                                    <VBtn color="primary" block @click="dialog = true">
                                        Registrar nueva mascota
                                    </VBtn>
                                </VListItem>
                            </div>
                        </template>
                    </VAutocomplete>
                </VCol>

                <VCol cols="4">
                    <AppDateTimePicker v-model="event_date" label="Fecha del servicio" placeholder="Select Fecha"
                        :config="{ mode: 'range' }" />

                </VCol>

                <VCol cols="4">
                    <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="search()">
                    </VBtn>

                    <VBtn color="secondary" prepend-icon="ri-restart-line" @click="reset()">
                    </VBtn>
                </VCol>
            </VRow>
        </VCard>
        <VRow class="my-2">
            <VCol cols="4">
                <VCard>
                    <VCardText class="text-center pt-12 pb-6">
                        <!-- 👉 Avatar -->
                        <VAvatar rounded="lg" :size="120" :color="'primary'" :variant="'tonal'">
                            <VImg
                                :src="'https://demos.pixinvent.com/materialize-vuejs-admin-template/demo-1/assets/avatar-3-DsWgWr0y.png'" />
                            <!--<span v-else class="text-5xl font-weight-medium">
                                {{ avatarText(props.userData.fullName) }}
                            </span>-->
                        </VAvatar>

                        <!-- 👉 User fullName -->
                        <h5 class="text-h5 mt-4">
                            ROcky
                        </h5>

                        <!-- 👉 Role chip -->
                        <VChip :color="'primary'" size="small" class="text-capitalize mt-4">
                            Pastor Aleman
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
                                    100
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
                                    50
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
                                    50
                                </h5>
                                <span> Cirugías</span>
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
                                    Rocky
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
                                    Masculino
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
                                    Pator
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
                                    Pator
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
                                    15/03/2025
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
                                    15 Kg
                                </div>
                            </div>
                        </div>

                        <div class="text-body-2 text-disabled mt-6 mb-4">
                            RESPONSABLE/DUEÑO
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-user-line" 
                                size="24" />
                                <div class="font-weight-medium">
                                    Nombres:
                                </div>
                                <div class="text-truncate">
                                   Kreisler Umbo Ruiz
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-cellphone-fill" 
                                size="24" />
                                <div class="font-weight-medium">
                                    Telefóno:
                                </div>
                                <div class="text-truncate">
                                  950 917 607
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-passport-line" 
                                size="24" />
                                <div class="font-weight-medium">
                                    Tipo Documento:
                                </div>
                                <div class="text-truncate">
                                  DNI
                                </div>
                            </div>
                        </div>
                         <div class="d-flex flex-column gap-y-4">
                            <div class="d-flex align-center gap-x-2">
                                <VIcon icon="ri-id-card-line" 
                                size="24" />
                                <div class="font-weight-medium">
                                    Nro Documento:
                                </div>
                                <div class="text-truncate">
                                  44359286
                                </div>
                            </div>
                        </div>
                    </VCardText>
                </VCard>
            </VCol>
            <VCol cols="8">
                <VCard title="Activity Timeline">
                    <VCardText>
                        <VTimeline side="end" align="start" line-inset="9" truncate-line="start" density="compact">
                            <!-- SECTION Timeline Item: Flight -->
                            <VTimelineItem size="x-small" dot-color="primary">
                                <!-- 👉 Header -->
                                <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                    <div class="app-timeline-title">
                                       Cita Médica 18/11/2025
                                       <VChip class="mx-3" color="warning">
                                        Pendiente
                                       </VChip>
                                    </div>
                                    <span class="app-timeline-meta">45 min ago</span>
                                </div>

                                <div class="app-timeline-text mt-3">
                                    La atención inicia @10:00am hasta @11:00am
                                    <br>
                                    Costo: 150 PEN
                                </div>

                                <!-- 👉 Person -->
                                <div class="d-flex justify-space-between align-center flex-wrap">
                                    <!-- 👉 Avatar & Personal Info -->
                                    <div class="d-flex align-center my-2">
                                        <VAvatar size="32" class="me-2" :image="avatar1" />
                                        <div class="d-flex flex-column">
                                            <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                Lester McCarthy (Veterinario)
                                            </p>
                                            <span class="text-sm">CEO of Pixinvent</span>
                                        </div>
                                    </div>
                                </div>
                            </VTimelineItem>
                            <!-- !SECTION -->

                            <!-- SECTION Timeline Item: Interview Schedule -->
                            <VTimelineItem size="x-small" dot-color="warning">
                                <!-- 👉 Header -->
                                <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                    <div class="app-timeline-title">
                                         Vacunación 15/11/2025
                                       <VChip class="mx-3" color="success">
                                        Atendido
                                       </VChip>
                                    </div>
                                    <span class="app-timeline-meta">45 min ago</span>
                                </div>

                                 <div class="app-timeline-text mt-3">
                                    La atención inicia @08:00am hasta @13:00am
                                    <br>
                                    Costo: 50 PEN
                                    <br>
                                    Lugar:
                                    Fuera de la Veterinaría
                                    <br>
                                    Proxima fecha Vacunacion:
                                    21/12/2025
                                </div>

                                <!-- 👉 Person -->
                                <div class="d-flex justify-space-between align-center flex-wrap">
                                    <!-- 👉 Avatar & Personal Info -->
                                    <div class="d-flex align-center my-2">
                                        <VAvatar size="32" class="me-2" :image="avatar1" />
                                        <div class="d-flex flex-column">
                                            <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                Lester McCarthy (Veterinario)
                                            </p>
                                            <span class="text-sm">CEO of Pixinvent</span>
                                        </div>
                                    </div>
                                </div>
                            </VTimelineItem>
                            <!-- !SECTION -->

                            <!-- SECTION Design Review -->
                            <VTimelineItem size="x-small" dot-color="success">
                                <!-- 👉 Header -->
                                <div class="d-flex justify-space-between align-center flex-wrap mb-2">
                                    <div class="app-timeline-title">
                                        Cirugía 11/11/2025
                                       <VChip class="mx-3" color="success">
                                        Atendido
                                       </VChip>
                                    </div>
                                    <span class="app-timeline-meta">45 min ago</span>
                                </div>

                                <div class="app-timeline-text mt-3">
                                  La atención inicia @08:00am hasta @13:00am
                                  <br>
                                    Costo: 50 PEN
                                    <br>
                                    Lugar:
                                    Fuera de la Veterinaría
                                    <br>
                                    Tipo de Cirugía:
                                    Traumatológica
                                </div>

                                <!-- 👉 Person -->
                                <div class="d-flex justify-space-between align-center flex-wrap">
                                    <!-- 👉 Avatar & Personal Info -->
                                    <div class="d-flex align-center my-2">
                                        <VAvatar size="32" class="me-2" :image="avatar1" />
                                        <div class="d-flex flex-column">
                                            <p class="text-sm font-weight-medium text-medium-emphasis mb-0">
                                                Lester McCarthy (Veterinario)
                                            </p>
                                            <span class="text-sm">CEO of Pixinvent</span>
                                        </div>
                                    </div>
                                </div>
                            </VTimelineItem>
                            <!-- !SECTION -->
                        </VTimeline>
                    </VCardText>
                </VCard>
            </VCol>
        </VRow>
    </div>
</template>