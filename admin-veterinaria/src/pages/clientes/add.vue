<script setup>
import { VCol } from 'vuetify/components';
import { definePage } from 'vue-router/auto';
import { $api } from '@/utils/api';
import { ref } from 'vue';
import { VBtn, VCard, VRow, VSelect, VTextField } from 'vuetify/components';

definePage({
    meta: {
        permissions: ['register_client'],
    },
});

const warning = ref(null);
const error_exist = ref(null);
const success = ref(null);
const router = useRouter();

const form = ref({
    razon_social: null,
    nombre_comercial: null,
    codigo: null,
    type_document: null,
    n_document: null,
    direccion_fiscal: null,
    email: null,
    telefono: null,
    celular: null,
    num_cuenta_detraccion: null,
    nombres: null,
    apellidos: null,
    fecha_registro: null,
})

const type_document = [
    'DNI',
    'RUC',
    'PASAPORTE',
    'CARNET EXTRANJERIA',
];

const fieldClean = () => {
    form.value.razon_social = null;
    form.value.nombre_comercial = null;
    form.value.codigo = null;
    form.value.type_document = null;
    form.value.n_document = null;
    form.value.direccion_fiscal = null;
    form.value.email = null;
    form.value.telefono = null;
    form.value.celular = null;
    form.value.num_cuenta_detraccion = null;
    form.value.nombres = null;
    form.value.apellidos = null;
    form.value.fecha_registro = null;
}

const store = async () => {
    try {
        let formData = new FormData();
        formData.append("razon_social", form.value.razon_social);
        formData.append("nombre_comercial", form.value.nombre_comercial);
        formData.append("codigo", form.value.codigo);
        formData.append("type_document", form.value.type_document);
        formData.append("n_document", form.value.n_document);
        formData.append("direccion_fiscal", form.value.direccion_fiscal);
        formData.append("email", form.value.email);
        formData.append("telefono", form.value.telefono);
        formData.append("celular", form.value.celular);
        formData.append("num_cuenta_detraccion", form.value.num_cuenta_detraccion);
        formData.append("nombres", form.value.nombres);
        formData.append("apellidos", form.value.apellidos);
        formData.append("fecha_registro", form.value.fecha_registro);


        const resp = await $api('/clients', {
            method: 'POST',
            body: formData,
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }
        })
        console.log(resp);
        success.value = 'Cliente registrada correctamente';
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


</script>
<template>
    <div>
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        REGISTRAR CLIENTE
                    </h4>
                </div>

                <VRow>
                    <VCol cols="4">
                        <VSelect :items="type_document" label="Tipo Documento" item-title="Tipo Documento"
                            item-value="id" placeholder="Seleccione Tipo Documento" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Nro Documento" type="number" v-model="form.n_document"
                            placeholder="Ingrese Nro Documento" />
                    </VCol>
                    <VCol cols="1">
                        <VBtn color="info" class="mx-1" prepend-icon="ri-search-2-line">
                        </VBtn>
                    </VCol>
                    <VCol cols="3">
                        <VTextField label="Codigo" type="text" v-model="form.codigo" placeholder="Ingrese Codigo" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField label="Nombres" type="text" v-model="form.nombres" placeholder="Ingrese Nombres" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField label="Apellidos" type="text" v-model="form.apellidos"
                            placeholder="Ingrese Apellidos" />
                    </VCol>

                    <VCol cols="6">
                        <VTextField label="Razon Social" type="text" v-model="form.razon_social"
                            placeholder="Ingrese Razon Social" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField label="Nombre Comercial" type="text" v-model="form.nombre_comercial"
                            placeholder="Ingrese Nombre Comercial" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField label="Direccion Fiscal" type="text" v-model="form.direccion_fiscal"
                            placeholder="Ingrese Direccion Fiscal" />
                    </VCol>
                    <VCol cols="6">
                        <VTextField label="Email" type="text" v-model="form.email" placeholder="Ingrese Email" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Telefono" type="text" v-model="form.telefono"
                            placeholder="Ingrese Telefono" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Celular" type="text" v-model="form.celular" placeholder="Ingrese Celular" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Nro Cuenta Detraccion" type="text" v-model="form.num_cuenta_detraccion"
                            placeholder="Ingrese Nro Cuenta Detraccion BN" />
                    </VCol>
                </VRow>
            </VCardText>
            <VCardText class="pa-5 py-0">
                <VBtn color="secondary" class="mx-1" prepend-icon="ri-arrow-left-line" @click="router.push({ name: 'clientes-list' })">
                    Listado
                </VBtn>

                <VBtn color="primary" class="mx-1" prepend-icon="ri-add-line" @click="store()">
                    Crear
                </VBtn>
            </VCardText>
        </VCard>
    </div>
</template>