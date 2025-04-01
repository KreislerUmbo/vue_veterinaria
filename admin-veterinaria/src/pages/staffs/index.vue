<script setup>
//import data from '@/views/js/datatable'
import { $api } from '@/utils/api';
import { onMounted, ref, watch } from 'vue';

const data = ref([]);

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

const roles = ref([]);

const avatarText = value => {
    if (!value)
        return ''
    const nameArray = value.split(' ')
    return nameArray.map(word => word.charAt(0).toUpperCase()).join('')

}

const searchQuery = ref(null);
const staff_selected = ref(null);
const staff_selected_deleted = ref(null);
const isAddStaffDialogVisible = ref(false);
const isEditStaffDialogVisible = ref(false);
const isDeleteStaffDialogVisible = ref(false);



const list = async () => {
    const resp = await $api('/staffs?search=' + (searchQuery.value ? searchQuery.value : ''), {
        method: 'GET',
        onResponseError({ response }) {
            console.log(response);
        }
    })
    console.log(resp);
    data.value = resp.users.data;
    roles.value = resp.roles;
}

const addUser = (newUser) => {
    data.value.unshift(newUser);//unshift ordena los recien agregados en primera fila
}

//PASO 06 para editar 
//declaramos la variable editUser que viene del paso 05. el paso 07 se cambia en el formulario pero sin agregar nada->
const editUser = (editUser) => {
    let INDEX = data.value.findIndex((user) => user.id == editUser.id); //PASO 10 para editar, agregamos todo para que refresque la pagina
    if (INDEX != -1) {
        data.value[INDEX] = editUser;
    }
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
    isEditStaffDialogVisible.value = true;
    staff_selected.value = item;
}
const deleteItem = (item) => {
    isDeleteStaffDialogVisible.value = true;
    staff_selected_deleted.value = item;
}

onMounted(() => {
    list(); // Cargar la lista de usuarios cuando el componente se monte
})

watch(isEditStaffDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        staff_selected.value = null;
    }
})

watch(isDeleteStaffDialogVisible, (event) => {
    console.log(event);
    if (event == false) {
        staff_selected_deleted.value = null;
    }
})
definePage({
  meta: {
    permisssion:'list_staff',
  },
})

</script>
<template>
    <div>
        <VCard title="Staffs">
            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <VTextField v-model="searchQuery" placeholder="Search Staffs" style="inline-size: 300px;"
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

                        Agregar Staff
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
            <AddStaffDialog v-if="roles.length > 0" v-model:is-dialog-visible="isAddStaffDialogVisible"
                :roles123="roles" @addUser="addUser" />
            <!-- PASO 05 para editar. cambiamos addUser por editUser que viene del EditStaffDialog.vue paso 04 y tambien userSelected de paso 01 -->
            <EditStaffDialog v-if="staff_selected" :userSelected="staff_selected" :roles123="roles"
                v-model:is-dialog-visible="isEditStaffDialogVisible" @editUser="editUser" />

            <DeleteStaffDialog v-if="staff_selected_deleted" :userSelected="staff_selected_deleted" @deleteUser="deleteUser"
                v-model:is-dialog-visible="isDeleteStaffDialogVisible"  />
        </VCard>
    </div>
</template>