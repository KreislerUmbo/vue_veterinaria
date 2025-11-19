<script setup>
import { p } from '@antfu/utils';
import { onMounted, ref } from 'vue'

const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    medicalRecord: {
        type: Object,
        required: false,
    },
})

const emit = defineEmits(['update:isDialogVisible', 'addPayment']);

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}
const method_payments = ref([
    'Efectivo',
    'Yape',
    'Plin',
    'Tarjeta',
    'Trasferencia',
    'Paypal',
])
const form = ref(
    {
        method_payment: 'Efectivo',
        amount: 0,
        notes: null,
        date_payment: null,

    }
);

const warning = ref(null);
const error_exist = ref(null);
const success = ref(null);
const medical_record_selected = ref(null);

const fieldClean = () => {
    form.value = {
        method_payment: 'Efectivo',
        amount: 0,
        notes: null,
        date_payment: null,
    }

}


const store = async () => {
    warning.value = null;

    if (!form.value.method_payment) {
        warning.value = "Debe ingresar el método de pago ";
        return;
    }
    if (!form.value.amount || form.value.amount <= 0) {
        warning.value = "Debe ingresar un monto válido";
        return;
    }
    let data = {
        method_payment: form.value.method_payment,
        amount: form.value.amount,
        notes: form.value.notes,
        date_payment: form.value.date_payment,

        appointment_id: medical_record_selected.value.appointment_id,
        vaccination_id: medical_record_selected.value.vaccination_id,
        surgerie_id: medical_record_selected.value.surgerie_id,
        medical_record_id: medical_record_selected.value.id,

    }
    try {
        const resp = await $api('/payments', {
            method: 'POST',
            body: data,
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);
        if (resp.message == 403) {
            warning.value = resp.message_text;
        } else {
            success.value = "EL PAGO SE HA CREADO EXITOSAMENTE";
        }
        setTimeout(() => {
            success.value = null;
            warning.value = null;
            error_exist.value = null;
            fieldClean();
            emit('update:isDialogVisible', false);
            emit('addPayment', resp.payment)//addPayment viene del padre list.vue y resp.payment es el nuevo pago creado
        }, 1500);

    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }
}
onMounted(() => {
    console.log(props.medicalRecord);
    medical_record_selected.value = props.medicalRecord;
});
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2">
                        AGREGAR PAGOS
                    </h4>
                </div>
                <VRow>
                    <VCol cols="4">
                        <VSelect v-model="form.method_payment" :items="method_payments" label="Metodo de Pago"
                            item-title="name" item-value="id" placeholder="Seleccione Metodo de Pago" />
                    </VCol>
                    <VCol cols="4">
                        <VTextField label="Monto:" v-model="form.amount" placeholder="ejemplo: 100" />
                    </VCol>

                    <VCol cols="4">
                        <AppDateTimePicker v-model="form.date_payment" label="Fecha Pago"
                            placeholder="Select Fecha Pago" append-to-body />
                    </VCol>

                    <VCol cols="12">
                        <VTextarea label="Agregar Nota" rows="3" v-model="form.notes"
                            placeholder="Ingrese alguna nota ejem: pago el sr Leonardo" />
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
