<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { onMounted, ref, watch } from 'vue';
import { definePage } from 'vue-router/auto';
import { VBtn, VCardText, VChip, VRow, VSelect, VTextField } from 'vuetify/components';


const router = useRouter();

const searchPets = ref(null);
const searchVeterinaries = ref(null);
const vaccinations = ref([]);

const specie = ref(null);
const species = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro', 'Conejo','Ave','Pez','Otro']);

const currentPage = ref(1)
const totalPage = ref(1)
const vaccionation_selected_deleted = ref(null);
const isDeleteVaccionationDialogVisible = ref(false);

const dateRange = ref(null);
const type_date = ref(1); // 1: fecha de cita, 2: fecha de registro
const state_pay = ref(null); // 1: pendiente de pago, 2: tiene adelantos, 3: completado
const state_vaccionation = ref(null); // 1: pendiente, 2: cancelado, 3: atendido

const list = async () => {
    let data = {
        type_date: type_date.value,
        start_date: dateRange.value ? dateRange.value.split("to")[0].trim() : null,
        end_date: dateRange.value ? dateRange.value.split("to")[1].trim() : null,
        state_pay: state_pay.value,
        state: state_vaccionation.value,
        specie: specie.value,
        search_pets: searchPets.value,
        search_vets: searchVeterinaries.value,
    }
    const resp = await $api('/vaccinations/index?page=' + currentPage.value, {// Llamada a la API para obtener la lista de citas
        method: 'POST',
        body: data,
        onResponseError({ response }) {
            console.log(response);
        }
    })
    console.log(resp);
    totalPage.value = resp.total_page;// Asignar el total de páginas a la variable reactiva. resp.total_page viene de la API
    vaccinations.value = resp.vaccinations.data;// Asignar la lista de citas a la variable reactiva. resp.vaccinations.data viene de la API
    console.log('API resp vaccinations index ->', resp);
    // Asignar total de páginas (soporta varias estructuras)
    totalPage.value = resp.total_page ?? resp.meta?.last_page ?? 1;// Asignar el total de páginas a la variable reactiva totalPage

    // Resolver lista de vacunaciones según la forma en que la API la devuelva
    // la diferencia entre resp.vaccinations.data y resp.data y resp.vaccinations.items  es que
    // la primera es cuando la API devuelve una colección paginada
    // y la segunda es cuando devuelve una lista simple
    // tmbien resp.vaccinations.items es cuando la API devuelve una colección con items
    // por ejemplo cuando se usa Resource Collections en Laravel
    // por eso se hace esta verificación para asignar correctamente la lista de vacunaciones
    // a la variable reactiva vaccinations
    // de esta manera el código es más robusto y puede manejar diferentes estructuras de respuesta de la API
    // y no romperse si la API cambia la forma en que devuelve los datos
    // esto es útil en desarrollo y mantenimiento de aplicaciones

    let vacData = [];// Inicializar vacData como un array vacío
    if (resp.vaccinations) {// Verificar si resp.vaccinations existe
        if (Array.isArray(resp.vaccinations)) {// Si es un array, asignarlo directamente
            vacData = resp.vaccinations;// Asignar resp.vaccinations directamente si es un array
        } else if (resp.vaccinations.data) {// Si tiene una propiedad data, asignar esa propiedad
            vacData = resp.vaccinations.data;
        } else if (resp.vaccinations.items) {// Si tiene una propiedad items, asignar esa propiedad
            vacData = resp.vaccinations.items;
        }
    } else if (resp.data && resp.data.vaccinations) {//
        vacData = resp.data.vaccinations;
    } else if (Array.isArray(resp.data)) {//
        vacData = resp.data;
    }

    vaccinations.value = vacData;
    console.log('resolved vaccinations ->', vaccinations.value);
}
const downloadExcel =  () => {
    let LINK = "";
    if (dateRange.value) {
        LINK += '&type_date=' + type_date.value;
        LINK += '&start_date=' + dateRange.value.split("to")[0];
        LINK += '&end_date=' + dateRange.value.split("to")[1];
    }
    if (state_pay.value) {
        LINK += '&state_pay=' + state_pay.value;
    }
    if (state_vaccionation.value) {
        LINK += '&state=' + state_vaccionation.value;
    }
    if (specie.value) {
        LINK += '&specie=' + specie.value;
    }
    if (searchPets.value) {
        LINK += '&search_pets=' + searchPets.value;
    }
    if(searchVeterinaries.value){
        LINK += '&search_vets=' + searchVeterinaries.value;
    }

    window.open(import.meta.env.VITE_API_BASE_URL + '/vaccination-excel?k=1' + LINK, '_blank');
}

const editItem = (item) => {
    router.push({ name: 'vaccination-edit-id', params: { id: item.id } });
}



const deleteVaccionation = (item) => {
    let INDEX = vaccinations.value.findIndex((vaccination) => vaccination.id == item.id);
    if (INDEX != -1) {
        vaccinations.value.splice(INDEX, 1);
    }

} 

const deleteItem = (item) => {
    vaccionation_selected_deleted.value = item;// Seleccionar la cita a eliminar
    isDeleteVaccionationDialogVisible.value = true;// Abrir el diálogo
}

const reset = () => {
    searchPets.value = null;
    searchVeterinaries.value = null;
    dateRange.value = null;
    specie.value = null;
    state_pay.value = null;
    state_vaccionation.value = null;
    type_date.value = 1;
    currentPage.value = 1;
    list();
}


const avatarText = value => {// Obtener las iniciales del nombre
    if (!value)
        return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}
watch(currentPage, (val) => {// Cuando cambie la página, recargar la lista
    console.log(val);
    list();
})

watch(isDeleteVaccionationDialogVisible, (val) => {// Cuando se cierre el diálogo, limpiar la cita seleccionada
    console.log(val);
    if (val == false) {
        vaccionation_selected_deleted.value = null;
    }
})

onMounted(() => {
    list(); // Cargar la lista de mascotitas
});

definePage({
    meta: {
        permissions: ['list_vaccionation'],
    },
});


</script>

<template>
    <div>
        <VCard title="Vacunaciones">
            <VCardText class="d-flex flex-wrap gap-4">
                <VRow>
                    <VCol cols="2">
                        <VSelect :items="[
                            {
                                name: 'Fecha de Vacunación',
                                id: 1
                            },
                            {
                                name: 'Fecha de Registro',
                                id: 2
                            }
                        ]" v-model="type_date" label="Tipo" item-title="name" item-value="id" placeholder="Select Tipo"
                            eager />
                    </VCol>

                    <VCol cols="2">
                        <AppDateTimePicker v-model="dateRange" label="Buscar por Rango de Fecha"
                            aria-placeholder="Seleccione Fecha" :config="{ mode: 'range' }" />
                    </VCol>
                    <VCol cols="2">
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
                        ]" v-model="state_vaccionation" label="Estado de la  Vacunación" item-title="name" item-value="id"
                            placeholder="Select Estado Vacunancion" eager />
                    </VCol>

                    <VCol cols="3">
                        <div class="d-flex align-center">
                            <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line" @click="list()">
                            </VBtn>

                            <VBtn color="secondary" prepend-icon="ri-restart-line" @click="reset()">
                            </VBtn>
                            <VBtn color="success" class="mx-1" prepend-icon="ri-file-excel-2-line"
                                @click="downloadExcel()">   
                            </VBtn>
                        </div>
                    </VCol>
                    <VCol cols="3">
                        <VBtn color="primary" prepend-icon="ri-add-line"
                            @click="router.push({ name: 'vaccination-add' })">
                            Agregar Vacunación
                        </VBtn>
                    </VCol>  
                    <VCol cols="2">
                        <VSelect :items="species" v-model="specie" style="inline-size: 150px;" label="Especie"
                            item-title="name" item-value="id" placeholder="Select Especie" eager />
                    </VCol>
                    <VCol cols="3">
                        <VTextField v-model="searchPets" label="Buscar por Nombre de Mascota o Veterinario"
                            placeholder="Buscar por Nombre de Mascota o Veterinario" density="compact" class="me-3"
                            @keyup.enter="list" />
                    </VCol>
                    <VCol cols="3">
                        <VTextField v-model="searchVeterinaries" label="Buscar por Veterinario"
                            placeholder="Buscar por Veterinario" density="compact" class="me-3" @keyup.enter="list" />
                    </VCol>
                    <VCol cols="2">
                        <VSelect :items="[
                            {
                                name: 'Pendite de pago',
                                id: 1
                            },
                            {
                                name: 'Tiene adelantos',
                                id: 2
                            },
                            {
                                name: 'Completado',
                                id: 3
                            }
                        ]" v-model="state_pay" label="Estado de pago" item-title="name" item-value="id"
                            placeholder="Select Estado" eager />
                    </VCol>
                </VRow>
            </VCardText>

            <VCardText class="pa-5">
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                Mascota
                            </th>
                            <th class="text-uppercase">
                                Especie
                            </th>
                            <th class="text-uppercase">
                                Fecha Vacunación
                            </th>
                            <th class="text-uppercase">
                                Veterinarios
                            </th>
                            <th class="text-uppercase">
                                Estado
                            </th>
                            <th class="text-uppercase">
                                Costo
                            </th>
                            <th class="text-uppercase">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in vaccinations" :key="item.id">
                            <td>
                                <div class="d-flex align-center">
                                    <VAvatar size="32" :color="item.pet.photo ? '' : 'primary'"
                                        :class="item.pet.photo ? '' : 'v-avatar-light-bg primary--text'"
                                        :variant="!item.pet.photo ? 'tonal' : undefined">
                                        <VImg v-if="item.pet.photo" :src="item.pet.photo" />
                                        <span v-else class="text-sm">{{ avatarText(item.pet.name) }}</span>
                                    </VAvatar>
                                    <div class="d-flex flex-column ms-3">
                                        <span class="d-block font-weight-medium text-high-emphasis text-truncate"> {{
                                            item.pet.name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ item.pet.specie }}
                            </td>
                            <td>
                                {{ item.vaccionation_date }}
                            </td>
                            <td>
                                {{ item.veterinarie.full_name }}
                            </td>
                            <td>
                                <VChip v-if="item.state == 3" color="success" size="small">Atendido</VChip>
                                <VChip v-else-if="item.state == 1" color="warning" size="small">Pendiente
                                </VChip>
                                <VChip v-else-if="item.state == 2" color="error" size="small">Cancelado
                                </VChip>
                            </td>
                            <td>
                                {{ item.amount }} PEN
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <IconBtn size="small" @click="editItem(item)">
                                        <VIcon icon="ri-pencil-line" />
                                    </IconBtn>

                                    <IconBtn size="small" @click="deleteItem(item)">
                                        <VIcon icon="ri-delete-bin-line" />
                                    </IconBtn>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
                <VPagination v-model="currentPage" :length="totalPage" />
            </VCardText>

            <DeleteVaccinationDialog v-if="vaccionation_selected_deleted"
                :vaccinationSelected="vaccionation_selected_deleted" @deleteVaccionation="deleteVaccionation"
                v-model:is-dialog-visible="isDeleteVaccionationDialogVisible" />
        </VCard>
    </div>
</template>
<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>