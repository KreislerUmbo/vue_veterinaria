<script setup>
import { onMounted, ref, watch } from 'vue'
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    surgerieSelected: {//viene de list.vue la cual contiene el objeto de la cita seleccionada
        type: Object,
        required: true,
    }
})

const emit = defineEmits(['update:isDialogVisible', 'deleteSurgerie'])//para referescar la pagina, deleteAppointment viene del boton eliminar que esta en list.vue

const error_exist = ref(null);
const success = ref(null);
const surgerie_selected = ref(null);

const deleted = async () => {
    try {
        const resp = await $api('/surgeries/' +surgerie_selected.value.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);
        if (resp.message == 403) {
            error_exist.value = "No se puede eliminar esta cirugia porque ya ha sido atendida.";
            return;
        } else {
            success.value = "La cirugia se ha eliminado con éxito.";
            emit('deleteSurgerie', surgerie_selected.value);//para referescar la pagina  y props sirve para obtener el objeto de la cita seleccionada

            // Cierra el modal después de un momento para que el usuario vea el mensaje de éxito.
            setTimeout(() => {
                emit('update:isDialogVisible', false);
            }, 1000);
        }

    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }
}
onMounted(() => {
surgerie_selected.value=props.surgerieSelected;
console.log(surgerie_selected.value);
});
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750"
        @update:model-value="val => emit('update:isDialogVisible', val)">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2" v-if="surgerie_selected">
                        Eliminar la cirugía para: {{ surgerie_selected.id }}
                    </h4>
                </div>
                <p v-if="surgerie_selected">
                    ¿Estás seguro de que deseas eliminar la cirugía para "{{ surgerie_selected.pet.name }}"? Esta
                    acción
                    no se puede deshacer.
                </p>

                <VAlert type="error" class="mt-3" v-if="error_exist">
                    <strong>{{ error_exist }}</strong>
                </VAlert>
                <VAlert type="warning" class="mt-3" v-if="success">
                    <strong>{{ success }}</strong>
                </VAlert>

            </VCardText>
            <VCardText class="d-flex justify-center flex-wrap gap-4">
                <VBtn variant="outlined" color="secondary" @click="emit('update:isDialogVisible', false)">
                    Cerrar
                </VBtn>
                <VBtn color="error" class="mb-4" @click="deleted()" :disabled="!!success">
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
