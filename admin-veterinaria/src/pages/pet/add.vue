<script setup>
import AppDateTimePicker from '@/@core/components/app-form-elements/AppDateTimePicker.vue';
import { $api } from '@/utils/api';
import { ref } from 'vue';
import { definePage } from 'vue-router/auto';
import { VBtn, VCard, VRow, VTextarea, VTextField } from 'vuetify/components';

const type_documents = [
    'DNI',
    'PASAPORTE',
    'CARNET EXTRANJERIA',
];
const warning = ref(null);
const error_exist = ref(null);
const success = ref(null);
const router = useRouter();

const form = ref({
    name: null,
    specie: null,
    dirth_date: null,
    breed: null, // raza de la mascota
    gender: null, //genero de la mascota
    color: null,
    weight: null, // peso de la mascota en kg
    medical_notes: null, // notas médicas de la mascota

    last_name: null,
    first_name: null,
    type_document: "DNI",
    n_document: null,
    phone: null,
    email: null,
    address: null,
    city: null,
    emergency_contact: null,
});

const species = ref(['Perro', 'Gato', 'Hámster', 'Loro', 'Tortuga', 'Vaca', 'Caballo', 'Cuy', 'Toro', 'Conejo']);
const FILE_AVATAR = ref(null);
const IMAGEN_PREVIZUALIZA = ref(null);


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

const fieldClean = () => {
    form.value.name = null;
    form.value.specie = null;
    form.value.dirth_date = null;
    form.value.breed = null;
    form.value.gender = null;
    form.value.color = null;
    form.value.weight = null;
    form.value.medical_notes = null;

    form.value.last_name = null;
    form.value.first_name = null;
    form.value.type_document = "DNI";
    form.value.n_document = null;
    form.value.phone = null;
    form.value.email = null;
    form.value.address = null;
    form.value.city = null;
    form.value.emergency_contact = null;
    FILE_AVATAR.value = null;
    IMAGEN_PREVIZUALIZA.value = null;

}


const store = async () => {
    try {
        warning.value = null;
        if (!form.value.name) {
            warning.value = "Debe llenar el nombre de la mascota";
            return;
        }
        if (!form.value.specie) {
            warning.value = "Debe seleccionar la especie de la mascota";
            return;
        }
        if (!form.value.breed) {
            warning.value = "Debe llenar la raza  de la mascota";
            return;
        }
        if (!form.value.dirth_date) {
            warning.value = "Debe llenar la fecha de nacimiento de la mascota";
        }
        if (!form.value.gender) {
            warning.value = "Debe llenar el genero de la mascota";
            return;
        }
        if (!form.value.color) {
            warning.value = "Debe llenar el color de la mascota";
            return;
        }
        if (!form.value.weight) {
            warning.value = "Debe llenar el peso de la mascota";
            return;
        }
        if (!FILE_AVATAR.value) {
            warning.value = "Debe agregar una foto de la mascota";
            return;
        }


        if (!form.value.first_name) {
            warning.value = "Debe llenar el nombre del responsable";
            return;
        }
        if (!form.value.last_name) {
            warning.value = "Debe llenar el apellido del responsable";
            return;
        }
        if (!form.value.type_document) {
            warning.value = "Debe seleccionar el tipo de documento del responsable";
            return;
        }
        if (!form.value.n_document) {
            warning.value = "Debe llenar el numero de documento del responsable";
            return;
        }
        if (!form.value.phone) {
            warning.value = "Debe llenar el telefono del responsable";
            return;
        }
        if (!form.value.address) {
            warning.value = "Debe llenar la direccion del responsable";
            return;
        }
        if (!form.value.emergency_contact) {
            warning.value = "Debe llenar un numero de contacto de emergencia";
            return;
        }

        let formData = new FormData();
        //para las mascotias
        formData.append("name", form.value.name);
        formData.append("specie", form.value.specie);
        formData.append("dirth_date", form.value.dirth_date);
        formData.append("breed", form.value.breed);
        formData.append("gender", form.value.gender);
        formData.append("color", form.value.color);
        formData.append("weight", form.value.weight);
        if (form.value.medical_notes) {
            formData.append("medical_notes", form.value.medical_notes);
        }

        formData.append("imagen", FILE_AVATAR.value);

        //para los responsables
        formData.append("first_name", form.value.first_name);
        formData.append("last_name", form.value.last_name);
        formData.append("type_document", form.value.type_document);
        formData.append("n_document", form.value.n_document);
        formData.append("phone", form.value.phone);
        if (form.value.email) {
            formData.append("email", form.value.email);
        }
        if (form.value.address) {
            formData.append("address", form.value.address);
        }
        if (form.value.city) {
            formData.append("city", form.value.city);
        }
        if (form.value.emergency_contact) {
            formData.append("emergency_contact", form.value.emergency_contact);
        }

        const resp = await $api('/pets', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }
        })
        console.log(resp);

        success.value = 'Mascota y responsable registrada correctamente';
        setTimeout(() => {
            success.value = null;
            warning.value = null;
            error_exist.value = null;
            fieldClean();
        }, 2000);


    } catch (error) {
        console.log(error);

    }


}

definePage({
    meta: {
        permissions: ['register_pet'],
    },
});

// Funcion

</script>

<template>
    <div>

        <VCardText class="pa-5">
            <div class="mb-1">
                <h4 class="text-h4 text-center mb-1">
                    AGREGAR PACIENTE(mascota)
                </h4>
            </div>
        </VCardText>
        <VCard title="Mascota:" class="pa-4">
            <VRow>
                <VCol cols="6">
                    <VTextField label="Nombres:" v-model="form.name" placeholder="Lazi, Kuqui" />
                </VCol>

                <VCol cols="3">
                    <VSelect :items="species" v-model="form.specie" label="Especie" item-title="name" item-value="id"
                        placeholder="Select Especie" eager />
                </VCol>

                <VCol cols="3">
                    <VTextField label="Raza:" v-model="form.breed" placeholder="Labrador" />
                </VCol>
                <VCol cols="3">
                    <!-- <AppDateTimePicker v-model="form.dirth_date" label="Cumpleaños" placeholder="Select Fecha" />--->
                    <label for="">Fecha de nacimiento</label>
                    <div class="app-picker-field">
                        <div
                            class="v-input v-input--horizontal v-input--center-affix v-input--density-comfortable v-locale--is-ltr position-relative v-text-field">
                            <div class="v-input__control">
                                <div
                                    class="v-field v-field--center-affix v-field--variant-outlined v-theme--light v-locale--is-ltr">
                                    <div class="v-field__field">
                                        <div class="v-field__input">
                                            <input type="date" class="flat-picker-custom-style flatpickr-input"
                                                v-model="form.dirth_date" style="opacity: 1;" id="">
                                        </div>
                                    </div>
                                    <div class="v-field__outline text-primary">
                                        <div class="v-field__outline__start"></div>
                                        <div class="v-field__outline__notch"><label
                                                class="v-label v-field-			label v-field-label--floating"
                                                aria-hidden="true" for="input-8" style="">Nombre</label></div>
                                        <div class="v-field__outline__end"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </VCol>
                <VCol cols="3">
                    <VRadioGroup v-model="form.gender" inline>
                        <VRadio label="Macho" value="M" />
                        <VRadio label="Hembra" value="F" />
                    </VRadioGroup>
                </VCol>

                <VCol cols="4">
                    <VTextarea v-model="form.color" rows="2" label="Color" placeholder="Describe color" />
                </VCol>

                <VCol cols="2">
                    <VTextField v-model="form.weight" label="Peso" type="number" placeholder="Ejemplo: 20" />
                </VCol>
                <VCol cols="5">
                    <VTextarea v-model="form.medical_notes" rows="5" label="Notas Médicas"
                        placeholder="Notas Médicas" />
                </VCol>
                <VCol cols="3">
                    <VFileInput label="Foto Mascota" @change="loadFile($event)" />
                </VCol>
                <VCol cols="4">
                    <VImg v-if="IMAGEN_PREVIZUALIZA" width="180" height="180" :src="IMAGEN_PREVIZUALIZA" />
                </VCol>


            </VRow>
        </VCard>

        <VAlert type="warning" class="mt-3" v-if="warning">
            <strong>{{ warning }}</strong>
        </VAlert>
        <VAlert type="error" class="mt-3" v-if="error_exist">
            <strong>En el servidor hubo un error al momento de guardar los datos</strong>
        </VAlert>
        <VAlert type="success" class="mt-3" v-if="success">
            <strong>{{ success }}</strong>
        </VAlert>


        <VCard title="Responsable:" class="my-2 pa-4">
            <VRow>
                <VCol cols="6">
                    <VTextField label="Nombres:" v-model="form.first_name" placeholder="Ejemplo: Luis" />
                </VCol>

                <VCol cols="6">
                    <VTextField label="Apellidos:" v-model="form.last_name" placeholder="Ejemplo: Aguilar Perez" />
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
                <VCol cols="6">
                    <VTextField v-model="form.email" label="Email" type="email" placeholder="johndoe@email.com" />
                </VCol>
                <VCol cols="6">
                    <VTextField label="Direccion:" v-model="form.address"
                        placeholder="Ejemplo: Jr. Santo Toribio 123" />
                </VCol>
                <VCol cols="4">
                    <VTextField label="Ciudad:" v-model="form.city" placeholder="Ejemplo: Tarapoto" />
                </VCol>
                <VCol cols="4">
                    <VTextField label="Contacto Emergencia:" v-model="form.emergency_contact"
                        placeholder="Ejemplo: 950 000 000" />
                </VCol>

            </VRow>
        </VCard>


        <VCardText class="pa-5 py-0">
            <VBtn class="mb-4" @click="router.push({ name: 'pet-list' })">
                Listado
            </VBtn>

            <VBtn color="primary" class="mb-4 " @click="store()">
                Crear
            </VBtn>
        </VCardText>

    </div>
</template>
<style>
.v-img__img--contain {
    object-fit: cover !important;
}
</style>