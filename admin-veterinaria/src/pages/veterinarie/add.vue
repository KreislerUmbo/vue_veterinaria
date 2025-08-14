<script setup>
import { $api } from '@/utils/api';
import { elements } from 'chart.js';
import { onMounted, ref } from 'vue';
import { definePage } from 'vue-router/auto';

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
const days = ref(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes']);
const hour_days = ref([]);
const selected_segment_time = ref([]);
const schedule_hours_veterinrie = ref([]);
const load_request=ref(null);

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
        hour_days.value = resp.schedule_hours_group;
    } catch (error) {

    }
}
// funcion para chekcer y deschequear en bloque por dias 
const selectSegmentTimeAll = ($event, segment_times, day) => {
    if ($event.target.checked) {
        //checkbok marcado
        segment_times.forEach(segment_time => {
            selectedSegmentTime($event, segment_time, day);
            let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
            if (INDEX == -1) {
                selected_segment_time.value.push(segment_time.id + '-' + day);
            }

        });

    } else {
        //checkbok desmarcado
        segment_times.forEach(segment_time => {
            selectedSegmentTime($event, segment_time, day);
            let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
            if (INDEX != -1) {
                selected_segment_time.value.splice(INDEX, 1);
            }
        })
    }
}

const selectSegmentTimeAllGroups = ($event, segment_times) => {
    if ($event.target.checked) {
        //checkbok marcado

        days.value.forEach((day) => {
            segment_times.forEach(segment_time => {
                selectedSegmentTime($event, segment_time, day);
                let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
                if (INDEX == -1) {
                    selected_segment_time.value.push(segment_time.id + '-' + day);
                }

            });
        })
    } else {
        //checkbok desmarcado
        days.value.forEach((day) => {
            segment_times.forEach(segment_time => {
                selectedSegmentTime($event, segment_time, day);
                let INDEX = selected_segment_time.value.findIndex(seg_time => seg_time == segment_time.id + '-' + day);
                if (INDEX != -1) {
                    selected_segment_time.value.splice(INDEX, 1);
                }
            })
        })
        // selectSegmentTimeAll=false;
    }
}


const selectedSegmentTime = ($event, segment_time, day) => {
    if ($event.target.checked) {
        //checkbok marcado
        let INDEX = schedule_hours_veterinrie.value.findIndex(seg_time => seg_time.id_seg == segment_time.id + '-' + day);
        if (INDEX == -1) {
            schedule_hours_veterinrie.value.push({ id_seg: segment_time.id + '-' + day, segment_time_id: segment_time.id, day: day, });
        }

    } else {
        //checkbok desmarcado
        let INDEX = schedule_hours_veterinrie.value.findIndex(seg_time => seg_time.id_seg == segment_time.id + '-' + day);
        if (INDEX != -1) {
            schedule_hours_veterinrie.value.splice(INDEX, 1);
        }
    }
}

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
    schedule_hours_veterinrie.value=[];
    selected_segment_time.value=[];
}



const store = async () => {
    warning.value = null;
    if (schedule_hours_veterinrie.value.length==0) {
        warning.value = "Debes progrmar disponibilidad laboral del veterinario para continuar";
        return;
    }
    if (!form.value.name) {
        warning.value = "Debe ingresar el nombre del veterinario para continuar";
        return;
    }
    if (!form.value.surname) {
        warning.value = "Debe ingresar el apellido del veterinario para continuar";
        return;
    }
    if (!form.value.phone) {
        warning.value = "Debe ingresar un nro de telefono ";
        return;
    }
    if (!form.value.gender) {
        warning.value = "Debe elegir el genero del veterinario ";
        return;
    }
    if (!form.value.role_id) {
        warning.value = "Debe seleccionar un rol para el veterinario";
        return;
    }
    if (!form.value.email) {
        warning.value = "Debe ingresar un correo ";
        return;
    }

    if (!form.value.password) {
        warning.value = "Debe seleccionar una contraseña para el veterinario";
        return;
    }
    if (!FILE_AVATAR.value) {
        warning.value = "Debe seleccionar una imagen del veterinario ";
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
    formData.append("schedule_hours_veterinrie",JSON.stringify(schedule_hours_veterinrie.value));

    try {
        load_request.value=true;
        const resp = await $api('/veterinaries', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);
        load_request.value=false;
        if (resp.message == 403) {
            warning.value = resp.message_text;
        } else {
            success.value = "EL VETERINARIOS SE HA CREADO CON EXITO";
        }
        setTimeout(() => {
            success.value = null;
            warning.value = null;
            error_exist.value = null;
            fieldClean();
        }, 1500);

    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }
}


onMounted(() => {
    config();
})

definePage({
    meta: {
        permisssion: 'register_veterinary',
    },
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

            <VCardText class="pa-5 py-0">
                <VBtn color="primary" class="mb-4" @click="store()">
                    Crear
                </VBtn>
            </VCardText>

            <VCardText class="pa-5">
                {{ selected_segment_time }}
                <VTable>
                    <thead>
                        <tr>
                            <th class="text-uppercase">
                                DIAS/HORA
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
                                <VCheckbox @click="selectSegmentTimeAllGroups($event, item.segments_time)"
                                    label="Todos" v-if="!load_request" />
                            </td>
                            <td v-for="(day, index) in days" :key="index">
                                <div class="demo-space-x my-2">
                                    <VCheckbox @click="selectSegmentTimeAll($event, item.segments_time, day)"
                                        label="Todos" />

                                    <template v-for="(segment_time, index) in item.segments_time" :key="index">
                                        <VCheckbox @click="selectedSegmentTime($event, segment_time, day)"
                                            v-model="selected_segment_time"
                                            :label="segment_time.hour_start_format + ' - ' + segment_time.hour_end_format"
                                            :value="segment_time.id + '-' + day" />
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
.v-selection-control .v-label {
    font-size: x-small;
}

.v-checkbox.v-input {
    margin: 0;
}
</style>