<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { onMounted, ref, watch } from 'vue';
import { definePage } from 'vue-router/auto';
import { VBtn, VCardText, VChip, VRow, VSelect, VTextField } from 'vuetify/components';


const router = useRouter();

const searchPets = ref(null);
const searchVeterinaries = ref(null);
const payments = ref([]);

const specie = ref(null);
const species = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro', 'Conejo', 'Ave', 'Pez', 'Otro']);
const type_services = ref([
    {
        id: 1,
        name: 'Citas Médicas',
    },
    {
        id: 2,
        name: 'Vacunas',
    },
    {
        id: 3,
        name: 'Cirugías'
    }
]);
const currentPage = ref(1)
const totalPage = ref(1)
const medical_record_selected_deleted = ref(null);
const isDeletePaymentDialogVisible = ref(false);
const isAddPaymentDialogVisible = ref(false); // Nuevo estado para el diálogo de agregar pago
const isEditPaymentDialogVisible = ref(false); // Nuevo estado para el diálogo de editar pago

const dateRange = ref(null);
const type_date = ref(1); // 1: fecha de cita, 2: fecha de registro
const state_pay = ref(null); // 1: pendiente de pago, 2: tiene adelantos, 3: completado
const state_surgerie = ref(null); // 1: pendiente, 2: cancelado, 3: atendido
const type_service = ref(null);
const medical_record_selected = ref(null);
const payment_edit_selected = ref(null); // Pago seleccionado para editar la cual se pasa al diálogo de edición 
const payment_delete_selected = ref(null);

const list = async () => {
    let data = {
        type_date: type_date.value,
        start_date: dateRange.value ? dateRange.value.split("to")[0].trim() : null,
        end_date: dateRange.value ? dateRange.value.split("to")[1].trim() : null,
        state_pay: state_pay.value,
        state: state_surgerie.value,
        specie: specie.value,
        search_pets: searchPets.value,
        search_vets: searchVeterinaries.value,
        type_service: type_service.value,
    }
    const resp = await $api('/payments/index?page=' + currentPage.value, {
        method: 'POST',
        body: data,
        onResponseError({ response }) {
            console.log(response);
        }
    })
    console.log(resp);
    totalPage.value = resp.total_page;
    payments.value = resp.medical_records.data;
}

const downloadExcel = () => {
    let LINK = "";
    if (dateRange.value) {
        LINK += '&type_date=' + type_date.value;
        LINK += '&start_date=' + dateRange.value.split("to")[0];
        LINK += '&end_date=' + dateRange.value.split("to")[1];
    }
    if (state_pay.value) {
        LINK += '&state_pay=' + state_pay.value;
    }
    if (state_surgerie.value) {
        LINK += '&state=' + state_surgerie.value;
    }
    if (specie.value) {
        LINK += '&specie=' + specie.value;
    }
    if (searchPets.value) {
        LINK += '&search_pets=' + searchPets.value;
    }
    if (searchVeterinaries.value) {
        LINK += '&search_vets=' + searchVeterinaries.value;
    }
    if (type_service.value) {
        LINK += '&type_service=' + type_service.value;
    }

    window.open(import.meta.env.VITE_API_BASE_URL + '/payments-excel?k=1' + LINK, '_blank');
}


// deleteItem es para abrir el diálogo de confirmación
const deleteItem = (item, payment) => {
    medical_record_selected_deleted.value = item;// Seleccionar la cita a eliminar
    isDeletePaymentDialogVisible.value = true;// Abrir el diálogo
    payment_delete_selected.value = payment;
}

const deletePayment = (updatedMedicalRecord) => {
    let INDEX = payments.value.findIndex((payment) => payment.id == updatedMedicalRecord.id);
    if (INDEX != -1) {
        updatedMedicalRecord.is_view = true;
        payments.value[INDEX] = updatedMedicalRecord;
    }

}



const reset = () => {
    searchPets.value = null;
    searchVeterinaries.value = null;
    dateRange.value = null;
    specie.value = null;
    state_pay.value = null;
    state_surgerie.value = null;
    type_date.value = 1;
    currentPage.value = 1;
    type_service.value = null;
    list();
}


const avatarText = value => {// Obtener las iniciales del nombre
    if (!value)
        return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}

const addPayment = (updatedMedicalRecord) => {
    console.log(updatedMedicalRecord);
    let INDEX = payments.value.findIndex((item) => item.id == updatedMedicalRecord.id);
    if (INDEX != -1) {
        updatedMedicalRecord.is_view = true;
        payments.value[INDEX] = updatedMedicalRecord;
    }
}

const createPayment = (item) => {
    medical_record_selected.value = item;
    isAddPaymentDialogVisible.value = true;
}

// Edición de pago
const editPayment = (item, payment) => {
    isEditPaymentDialogVisible.value = true;
    payment_edit_selected.value = payment; // Pasar el pago seleccionado al diálogo de edición
    medical_record_selected.value = item; // Pasar el registro médico seleccionado al diálogo de edición

}

const updatePayment = (updatedMedicalRecord) => {
    console.log(updatedMedicalRecord);
    let INDEX = payments.value.findIndex((item) => item.id == updatedMedicalRecord.id);
    if (INDEX != -1) {// Si existe, actualizar el registro
        updatedMedicalRecord.is_view = true;
        payments.value[INDEX] = updatedMedicalRecord;
    }
}

watch(currentPage, (val) => {// Cuando cambie la página, recargar la lista
    console.log(val);
    list();
})

watch(isDeletePaymentDialogVisible, (val) => {// Cuando se cierre el diálogo, limpiar la cita seleccionad
    if (val == false) {
        medical_record_selected_deleted.value = null;
        payment_delete_selected.value = null;
    }
})
watch(isAddPaymentDialogVisible, (val) => {
    if (val == false) {
        medical_record_selected.value = null;
    }
})
watch(isEditPaymentDialogVisible, (val) => {
    if (val == false) {
        medical_record_selected.value = null;
        payment_edit_selected.value = null;
    }
})

onMounted(() => {
    list();
});

definePage({
    meta: {
        permissions: ['show_payment'],
    },
});


</script>

<template>
    <div>
        <VCard title="💰 Pagos 💸">
            <VCardText class="d-flex flex-wrap gap-4">
                <VRow>

                    <VCol cols="2">
                        <VSelect :items="type_services" v-model="type_service" label="Tip. Servicio" item-title="name"
                            item-value="id" placeholder="Select Tipo" eager />
                    </VCol>
                    <VCol cols="2">

                        <VSelect :items="[
                            {
                                name: 'Fecha del servicio',
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
                    <VCol cols="2" v-if="type_service">
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
                        ]" v-model="state_surgerie" label="Estado del servicio" item-title="name" item-value="id"
                            placeholder="Select Estado Servicio" eager />
                    </VCol>

                    <VCol cols="4">
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

                    <VCol cols="2">
                        <VSelect :items="species" v-model="specie" style="inline-size: 150px;" label="Especie"
                            item-title="name" item-value="id" placeholder="Select Especie" eager />
                    </VCol>
                    <VCol cols="4">
                        <VTextField v-model="searchPets" label="Buscar por Nombre de Mascota o Veterinario"
                            placeholder="Buscar por Nombre de Mascota o Veterinario" density="compact" class="me-3"
                            @keyup.enter="list" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField v-model="searchVeterinaries" label="Buscar por Veterinario"
                            placeholder="Buscar por Veterinario" density="compact" class="me-3" @keyup.enter="list" />
                    </VCol>
                    <VCol cols="2" v-if="type_service">
                        <VSelect :items="[
                            {
                                name: 'Pendiente de pago',
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
                                Fecha servicio
                            </th>
                            <th class="text-uppercase">
                                Tipo servicio
                            </th>
                            <th class="text-uppercase">
                                Veterinarios
                            </th>
                            <th class="text-uppercase">
                                Costo
                            </th>
                            <th class="text-uppercase">
                                Pagos Realizados
                            </th>

                            <th class="text-uppercase">
                                Estado de pago
                            </th>
                            <th class="text-uppercase">
                                Acciones
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-for="item in payments" :key="item.id">
                            <tr>
                                <td>
                                    <div class="d-flex align-center">
                                        <VBtn icon="ri-eye-line" variant="text" size="small" class="me-2"
                                            toltip="Ver pagos">
                                            <VIcon icon="ri-eye-line" @click="item.is_view = !item.is_view" />
                                        </VBtn>

                                        <VAvatar size="32" :color="item.pet.photo ? '' : 'primary'"
                                            :class="item.pet.photo ? '' : 'v-avatar-light-bg primary--text'"
                                            :variant="!item.pet.photo ? 'tonal' : undefined">
                                            <VImg v-if="item.pet.photo" :src="item.pet.photo" />
                                            <span v-else class="text-sm">{{ avatarText(item.pet.name) }}</span>
                                        </VAvatar>
                                        <div class="d-flex flex-column ms-3">
                                            <span class="d-block font-weight-medium text-high-emphasis text-truncate">
                                                {{
                                                    item.pet.name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ item.pet.specie }}
                                </td>
                                <td style="text-wrap-mode: nowrap;">
                                    {{ item.event_date.split('-').reverse().join('-') }}
                                </td>
                                <td>
                                    <VChip v-if="item.event_type == 1" color="primary" size="small">Cita Médica</VChip>
                                    <VChip v-if="item.event_type == 2" color="warning" size="small">Vacunación</VChip>
                                    <VChip v-if="item.event_type == 3" color="success" size="small">Cirugía</VChip>
                                </td>
                                <td>
                                    {{ item.veterinarie.full_name }}
                                </td>
                                <td style="text-wrap-mode: nowrap;">
                                    {{ item.amount }} PEN
                                </td>
                                <td style="text-wrap-mode: nowrap;">
                                    <!--    <VChip v-if="item.state == 3" color="success" size="small">Atendido</VChip>
                                <VChip v-else-if="item.state == 1" color="warning" size="small">Pendiente
                                </VChip>
                                <VChip v-else-if="item.state == 2" color="error" size="small">Cancelado
                                </VChip>-->
                                    {{ item.payment_total }} PEN

                                </td>

                                <td>
                                    <VChip v-if="item.state_pay == 3" color="success" size="small">Completado</VChip>
                                    <VChip v-if="item.state_pay == 2" color="warning" size="small">Tiene adelantos
                                    </VChip>
                                    <VChip v-if="item.state_pay == 1" color="error" size="small">Pendiente de pago
                                    </VChip>
                                </td>
                                <td>
                                    <VBtn icon="ri-add-line" color="primary" variant="text"
                                        @click="createPayment(item)">
                                    </VBtn>

                                </td>
                            </tr>
                            <template v-for="(payment, index2) in item.payments" :key="index2">
                                <tr v-if="item.is_view">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ payment.method_payment }}</td>
                                    <td>{{ payment.date_payment.split('-').reverse().join('-') }}</td>
                                    <td>{{ payment.amount }} PEN</td>
                                    <td>
                                        <div class="d-flex-gap-1">
                                            <IconBtn size="small" @click="editPayment(item, payment)">
                                                <VIcon icon="ri-pencil-line" />
                                            </IconBtn>

                                            <IconBtn size="small" @click="deleteItem(item, payment)">
                                                <VIcon icon="ri-delete-bin-line" />
                                            </IconBtn>

                                        </div>

                                    </td>
                                </tr>
                            </template>

                        </template>

                    </tbody>
                </VTable>
                <VPagination v-model="currentPage" :length="totalPage" />
            </VCardText>

            <AddPaymentDialog v-if="medical_record_selected" @addPayment="addPayment"
                :medicalRecord="medical_record_selected" v-model:is-dialog-visible="isAddPaymentDialogVisible" />

            <EditPaymentDialog v-if="medical_record_selected && payment_edit_selected"
                :medicalRecord="medical_record_selected" :paymentSelected="payment_edit_selected"
                v-model:is-dialog-visible="isEditPaymentDialogVisible" @editPayment="updatePayment" />

            <DeletePaymentDialog v-if="medical_record_selected_deleted" :medicalRecord="medical_record_selected_deleted"
                :paymentSelected="payment_delete_selected" @deletePayment="deletePayment"
                v-model:is-dialog-visible="isDeletePaymentDialogVisible" />
        </VCard>
    </div>
</template>
<style>
.v-btn__prepend {
    margin-inline: 0 !important;
}
</style>