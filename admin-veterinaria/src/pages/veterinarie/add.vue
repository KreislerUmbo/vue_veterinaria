<script setup>
import { $api } from '@/utils/api';
import { onMounted, ref } from 'vue';

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
const days = ref(['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes']);
const hour_days = ref([]);
const selected_segment_time=ref([]);

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

const config = async () => {
    try {
        const resp = await $api("/veterinaries/config", {
            method: 'GET',
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }
        })
        console.log(resp);
        roles.value = resp.roles;
        hour_days.value=resp.schedule_hours_group;
    } catch (error) {

    }
}

onMounted(() => {
    config();
})


</script>
<template>
    <div>
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        AGREGAR VETERINARIO
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
                        <VAutocomplete label="Rol" :items="roles" v-model="form.role_id" item-title="name"
                            item-value="id" placeholder="Select Rol" eager />
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
                {{ selected_segment_time }}
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                DIAS/HORAS
                            </th>
                            <th class="text-uppercase" v-for="(day, index) in days" :key="index">
                                {{ day }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in hour_days" :key="item.hour">
                            <td>
                                {{ item.hour_format }}
                            </td>
                            <td v-for="(day, index) in days" key="index">
                                <div class="demo-space-x my-2">
                                    <VCheckbox  label="Todos" value="John" />

                                    <template v-for="(segment_time,index) in item.segments_time":key="index">
                                        <VCheckbox 
                                        v-model="selected_segment_time" 
                                        :label="segment_time.hour_start_format+' - '+segment_time.hour_end_format" 
                                        :value="segment_time.id+'-'+day" />
                                    </template>
                                    
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </div>
</template>
<style>
    .v-selection-control .v-label{
        font-size: x-small;
    }
    .v-checkbox.v-input{
        margin: 0;
    }
</style>