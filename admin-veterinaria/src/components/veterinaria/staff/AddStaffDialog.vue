<script setup>
import { PERMISOS } from '@/utils/constants'
import { onMounted, ref } from 'vue'
import index from 'vue-prism-component'
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    roles123: {
        type: Object,
        required: true,
    }
})

const emit = defineEmits(['update:isDialogVisible', 'addUser'])

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}

const form = ref(
    {
        name: null,
        surname: null,
        email: null,
        phone: null,
        type_document: null,
        n_document: null,
        birthday: null,
        password: null,
        designation: null,
        gender: null,
        role_id: null,

    }
);

const type_documents = [
    'DNI',
    'PASAPORTE',
    'CARNET EXTRANJERIA',
];
const warning = ref(null);
const error_exist = ref(null);
const success = ref(null);
const isPasswordVisible = ref(false);
const FILE_AVATAR = ref(null);
const IMAGEN_PREVIZUALIZA = ref(null);
const roles = ref([]);


const fieldClean = () => {
    form.value = {
        name: null,
        surname: null,
        email: null,
        phone: null,
        type_document: null,
        n_document: null,
        birthday: null,
        password: null,
        designation: null,
        gender: null,
        role_id: null,
    }
    FILE_AVATAR.value = null;
    IMAGEN_PREVIZUALIZA.value = null;
}



const store = async () => {
    warning.value = null;
    if (!form.value.name) {
        warning.value = "Debe ingresar el nombre del usuario para continuar";
        return;
    }
    if (!form.value.name) {
        warning.value = "Debe ingresar el apellido del usuario para continuar";
        return;
    }
    if (!form.value.phone) {
        warning.value = "Debe ingresar un nro de telefono ";
        return;
    }
    if (!form.value.gender) {
        warning.value = "Debe elegir el genero del usuario ";
        return;
    }
    if (!form.value.role_id) {
        warning.value = "Debe seleccionar un rol para el usuario";
        return;
    }
    if (!form.value.email) {
        warning.value = "Debe ingresar un correo ";
        return;
    }

    if (!form.value.password) {
        warning.value = "Debe seleccionar una contraseña para el usuario";
        return;
    }
    if (!FILE_AVATAR.value) {
        warning.value = "Debe seleccionar una imagen del usuario ";
        return;
    }
    //nota se usa fromData cuando en el formulario hay imagenes, y data cuando no hay img en el formulario
    let formData = new FormData();
    formData.append("name", form.value.name);
    formData.append("surname", form.value.surname);
    if (form.value.type_document) {
        formData.append("type_document", form.value.type_document);
    }
    if (form.value.n_document) {
        formData.append("n_document", form.value.n_document);
    }
    formData.append("phone", form.value.phone);
    formData.append("gender", form.value.gender);
    formData.append("role_id", form.value.role_id);
    formData.append("birthday", form.value.birthday);

    if (form.value.designation) {
        formData.append("designation", form.value.designation);
    }
    formData.append("email", form.value.email);
    formData.append("password", form.value.password);

    formData.append("imagen", FILE_AVATAR.value);

    try {
        const resp = await $api('/staffs', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);
        if (resp.message == 403) {
            warning.value = resp.message_text;
        } else {
            success.value = "EL USUARIO SE HA CREADO CON EXITO";
        }
        setTimeout(() => {
            success.value = null;
            warning.value = null;
            error_exist.value = null;
            fieldClean();
            emit('update:isDialogVisible', false);
            emit('addUser', resp.user)
        }, 1500);

    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }



}

const loadFile = ($event) => {
    if ($event.target.files[0].type.indexOf("image") < 0) {
        FILE_AVATAR.value = null;
        IMAGEN_PREVIZUALIZA.value = null;
        warning.value = "SOLAMENTE PUEDEN SER ARCHIVOS DE TIPO IMAGEN";
        return;
    }
    warning.value = '';
    FILE_AVATAR.value = $event.target.files[0];
    let reader = new FileReader();
    reader.readAsDataURL(FILE_AVATAR.value);
    reader.onloadend = () => IMAGEN_PREVIZUALIZA.value = reader.result;
}

onMounted(() => {
    roles.value = props.roles123;
})
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        AGREGAR USUARIO
                    </h4>
                </div>

                <VRow>
                    <VCol cols="6">
                        <VTextField label="Nombres:" v-model="form.name" placeholder="Ejemplo: Luis" />
                    </VCol>

                    <VCol cols="6">
                        <VTextField label="Apellidos:" v-model="form.surname" placeholder="Ejemplo: Aguilar Perez" />
                    </VCol>

                    <VCol cols="4">
                        <VSelect :items="type_documents" v-model="form.type_document" label="Tipo Documento"
                            placeholder="Select Tipo Document" eager />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Nro Documento:" type="number" v-model="form.n_document"
                            placeholder="Ejemplo: 44359284" />
                    </VCol>

                    <VCol cols="4">
                        <VTextField label="Teléfonos:" type="number" v-model="form.phone"
                            placeholder="Ejemplo: 950 000 000" />
                    </VCol>

                    <VCol cols="4">

                        <AppDateTimePicker v-model="form.birthday" label="Cumpleaños" placeholder="Select Fecha"
                            :config="{ enableTime: true, dateFormat: 'Y-m-d H:i' }" />
                    </VCol>

                    <VCol cols="4">
                        <VTextarea label="Designación" rows="1" placeholder="Designancion" />
                    </VCol>

                    <VCol cols="4">
<!--                         <VSelect :items="roles" v-model="form.role_id" label="Rol" item-title="name" item-value="id"
                            placeholder="Select Rol" eager /> -->
                        <VAutocomplete label="Rol" :items="roles" v-model="form.role_id" item-title="name" item-value="id" placeholder="Select Rol" eager />
                    </VCol>


                    <VCol cols="12">
                        <VRadioGroup v-model="form.gender" inline>
                            <VRadio label="Masculino" value="M" />
                            <VRadio label="Femenino" value="F" />
                        </VRadioGroup>
                    </VCol>

                    <VCol cols="6">
                        <VTextField v-model="form.email" label="Email" type="email" placeholder="johndoe@email.com" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField v-model="form.password" label="Password" placeholder="············"
                            :type="isPasswordVisible ? 'text' : 'password'"
                            :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                            @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                    </VCol>

                    <VCol cols="6">
                        <VFileInput label="Photo User" @change="loadFile($event)" />
                    </VCol>
                    <VCol cols="6" v-if="IMAGEN_PREVIZUALIZA">
                        <VImg width="137" height="176" :src="IMAGEN_PREVIZUALIZA" />
                    </VCol>

                </VRow>

                <VAlert type="warning" class="mt-3" v-if="warning">
                    <strong>{{ warning }}</strong>
                </VAlert>
                <VAlert type="error" class="mt-3" v-if="error_exist">
                    <strong>En el servidor hubo un error al momento de guardar los datos</strong>
                </VAlert>
                <VAlert type="success" class="mt-3" v-if="success">
                    <strong>{{ success }}</strong>
                </VAlert>

            </VCardText>
            <VCardText class="pa-5">
                <VBtn color="primary" class="mb-4" @click="store()">
                    Crear
                </VBtn>

            </VCardText>
        </VCard>
    </VDialog>
</template>

<style lang="scss">
.refer-link-input {
    .v-field--appended {
        padding-inline-end: 0;
    }

    .v-field__append-inner {
        padding-block-start: 0.125rem;
    }
}
</style>
