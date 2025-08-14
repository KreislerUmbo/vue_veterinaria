<script setup>
//import data from '@/views/js/datatable'
import { $api } from '@/utils/api';
import { onMounted, ref, watch } from 'vue';

const data = ref([]);
const router = useRouter();
const headers = [
    { title: 'ID', key: 'id' },
    { title: 'FOTO', key: 'imagen' },
    { title: 'NOMBRES Y APELLIDOS', key: 'full_name' },
    { title: 'ROL', key: 'role.name' },
    { title: 'CORREO', key: 'email' },
    { title: 'TELEFONO', key: 'phone' },
    { title: 'DOCUMENTO', key: 'document_full' },
    // { title: 'NRO. DOCUMENTO', key: 'n_document' },
    { title: 'OPE', key: 'action' },
]


const avatarText = value => {
    if (!value)
        return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')

}

const searchQuery = ref(null);
const staff_selected_deleted = ref(null);
const isDeleteStaffDialogVisible = ref(false);



const list = async () => {
    const resp = await $api('/veterinaries?search=' + (searchQuery.value ? searchQuery.value : ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response);
        }
    })
    console.log(resp);
    data.value = resp.veterinaries.data;
}


// Función para eliminar usuario
const deleteUser = (User) => {
  const INDEX = data.value.findIndex((user) => user.id == User.id);
  if (INDEX !== -1) {
    data.value.splice(INDEX, 1); // Eliminamos al usuario
  }
  // Aquí aseguramos que el modal se cierre y el listado se actualice
  isDeleteStaffDialogVisible.value = false;  // Cierra el modal de eliminación
  staff_selected_deleted.value = null;  // Limpiar la selección del usuario eliminado
}

const editItem = (item) => {
router.push({name:'veterinarie-edit-id',params:{id:item.id}});
}
const deleteItem = (item) => {
    isDeleteStaffDialogVisible.value = true;
    staff_selected_deleted.value = item;
}

onMounted(() => {
    list(); // Cargar la lista de usuarios cuando el componente se monte
})


watch(isDeleteStaffDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        staff_selected_deleted.value = null;
    }
})
definePage({
  meta: {
    permisssion:'list_veterinary',
  },
})

</script>
<template>
    <div>
        <VCard title="Veterinarios">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <VTextField v-model="searchQuery" placeholder="Search Veterinarios" style="inline-size: 300px;"
                        density="compact" class="me-3" @keyup.enter="list" />
                </div>

                <VSpacer />

                <div class="d-flex gap-x-4 align-center">
                    <!-- 👉 Export button -->
                    <!--                     <VBtn variant="outlined" color="secondary" prepend-icon="ri-upload-2-line">
                        Export
                    </VBtn> -->

                    <VBtn color="primary" prepend-icon="ri-add-line"
                        @click="router.push('/veterinaries-add')">

                        Agregar Veterinario
                    </VBtn>
                </div>
            </VCardText>

            <VDataTable :headers="headers" :items="data" :items-per-page="5" class="text-no-wrap">

                <template #item.id="{ item }">
                    <span class="text-h6">{{ item.id }}</span>
                </template>

                <!-- full name y avatar -->
                <template #item.imagen="{ item }">
                    <div class="d-flex align-center">
                        <VAvatar size="32" :color="item.avatar ? '' : 'primary'"
                            :class="item.avatar ? '' : 'v-avatar-light-bg primary--text'"
                            :variant="!item.avatar ? 'tonal' : undefined">
                            <VImg v-if="item.avatar" :src="item.avatar" />
                            <span v-else class="text-sm">{{ avatarText(item.full_name) }}</span>
                        </VAvatar>
                    </div>
                </template>
                <!-- type_document -->
                <template #item.document_full="{ item }">
                    <div class="d-flex align-center">
                        <div class="d-flex flex-column ms-3">
                            <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.n_document
                            }}</span>
                            <small>{{ item.type_document }}</small>
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
            <DeleteVeterinarioDialog v-if="staff_selected_deleted" :userSelected="staff_selected_deleted" @deleteUser="deleteUser"
                v-model:is-dialog-visible="isDeleteStaffDialogVisible"  />
        </VCard>
    </div>
</template>