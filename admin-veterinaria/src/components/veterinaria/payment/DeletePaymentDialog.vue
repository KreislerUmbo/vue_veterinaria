<script setup>
import { PERMISOS } from '@/utils/constants'
import { onMounted, ref } from 'vue'
import index from 'vue-prism-component'
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    medicalRecord: {
        type: Object,
        required: true,
    },
    paymentSelected: {
        type: Object,
        required: true,
    }


})

const emit = defineEmits(['update:isDialogVisible', 'deletePayment'])

const dialogVisibleUpdate = val => {
    emit('update:isDialogVisible', val)
}



const warning = ref(null);
const error_exist = ref(null);
const medical_record_selected = ref(null);
const success = ref(null);



const deleted = async () => {
    try {
        const resp = await $api('/payments/' + props.paymentSelected.id +
            "?appointment_id=" + (medical_record_selected.value.appointment_id ?? '') +
            "&vaccination_id=" + (medical_record_selected.value.vaccination_id ?? '') +
            "&surgerie_id=" + (medical_record_selected.value.surgerie_id ?? '') +
            "&medical_record_id=" +medical_record_selected.value.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);

        success.value = "EL PAGO SE HA ELIMINADO CON EXITO";
        emit('deletePayment', resp.payment);//para referescar la pagina 
        emit('update:isDialogVisible', false);//cierrra la ventana del modal
    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }
}

onMounted(() => {
    medical_record_selected.value = props.medicalRecord;
    console.log(medical_record_selected.value, props.paymentSelected);
})

</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="dialogVisibleUpdate">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2" v-if="medical_record_selected">
                        Eliminar el pago de : {{ props.paymentSelected.id + ' ' + props.paymentSelected.method_payment }}
                    </h4>
                </div>
                <p v-if="medical_record_selected">
                    ¿Estas seguro de eliminar el pago: "{{ props.paymentSelected.amount }} PEN"?
                </p>

                <VAlert type="error" class="mt-3" v-if="error_exist">
                    <strong>En el servidor hubo un error al momento al eliminar los datos</strong>
                </VAlert>
                <VAlert type="warning" class="mt-3" v-if="success">
                    <strong>{{ success }}</strong>
                </VAlert>

            </VCardText>
            <VCardText class="d-flex justify-center flex-wrap gap-4">
                <VBtn variant="outlined" color="secondary" @click="emit('update:isDialogVisible', false)">
                    Cerrar
                </VBtn>
                <VBtn color="error" class="mb-4" @click="deleted()">
                    Eliminar
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
