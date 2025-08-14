<script setup>
//import data from '@/views/js/datatable'
import { $api } from '@/utils/api';
import { onMounted, ref, watch } from 'vue';

const data = ref([]);

const headers = [
    { title: 'ID', key: 'id' },
    { title: 'NOMBRES', key: 'nombres' },
    { title: 'APELLIDOS', key: 'apellidos' },
    { title: 'FECHA', key: 'num_doc' },
    { title: 'OPE', key: 'action' },
]

//const roles = ref([]);

/*  const avatarText = value => {
    if (!value)
        return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')

}
 */
const searchQuery = ref(null);
const staff_selected = ref(null);
const staff_selected_deleted = ref(null);
const isAddStaffDialogVisible = ref(false);
const isEditStaffDialogVisible = ref(false);
const isDeleteStaffDialogVisible = ref(false);



 const list = async () => {
    const resp = await $api('clientes?search=' + (searchQuery.value ? searchQuery.value : ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response);
        }
    })
    console.log(resp);
    data.value = resp.clients.data;
} 

onMounted(() => {
    list(); // Cargar la lista de usuarios cuando el componente se monte
})
</script>
<template>
    <div>
        <VCard title="Clientes">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <VTextField v-model="searchQuery" placeholder="Search Clients" style="inline-size: 300px;"
                        density="compact" class="me-3" @keyup.enter="list" />
                </div>

                <VSpacer />

                <div class="d-flex gap-x-4 align-center">
                    <!-- 👉 Export button -->
                    <!--                     <VBtn variant="outlined" color="secondary" prepend-icon="ri-upload-2-line">
                        Export
                    </VBtn> -->

                    <VBtn color="primary" prepend-icon="ri-add-line"
                        @click="isAddStaffDialogVisible = !isAddStaffDialogVisible">

                        Agregar Cliente
                    </VBtn>
                </div>
            </VCardText>

            <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">

                <template #item.id="{ item }">
                    <span class="text-h6">{{ item.id }}</span>
                </template>

                <!-- type_document -->
                <template>
                    <div class="d-flex align-center">
                        <div class="d-flex flex-column ms-3">
                            <span class="d-block font-weight-medium text-high-emphasis text-truncate"></span>
                            <small></small>
                        </div>
                    </div>
                </template>


                <template #item.action="{ item }">
                    <div class="d-flex gap-1">
                        <IconBtn size="small" @click="editItem(item)">
                            <VIcon icon="ri-pencil-line" />
                        </IconBtn>

                        <IconBtn size="small" @click="deleteItem(item)">
                            <VIcon icon="ri-delete-bin-line" />
                        </IconBtn>
                    </div>
                </template>
            </VDataTable>
        </VCard>
    </div>
</template>