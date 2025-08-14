<script setup>
import { ref, watch } from 'vue'
const props = defineProps({
    isDialogVisible: {
        type: Boolean,
        required: true,
    },
    petSelected: {
        type: Object,
        required: true,
    }
})

const emit = defineEmits(['update:isDialogVisible', 'deletePet'])


const error_exist = ref(null);
const success = ref(null);

const deleted = async () => {
    try {
        const resp = await $api('/pets/' + props.petSelected.id, {
            method: 'DELETE',
            onResponseError({ response }) {
                console.log(response);
                error_exist.value = response._data.error;
            }

        })
        console.log(resp);

        success.value = "La mascota se ha eliminado con éxito.";
        emit('deletePet', props.petSelected);//para referescar la pagina
        
        // Cierra el modal después de un momento para que el usuario vea el mensaje de éxito.
        setTimeout(() => {
            emit('update:isDialogVisible', false);
        }, 1000);
    } catch (error) {
        console.log(error);
        error_exist.value = error;
    }
}

watch(() => props.isDialogVisible, (newValue) => {
    if (!newValue) {
        // Reset states when dialog is closed
        error_exist.value = null;
        success.value = null;
    }
})
</script>

<template>
    <VDialog :model-value="props.isDialogVisible" max-width="750" @update:model-value="val => emit('update:isDialogVisible', val)">
        <VCard class="refer-and-earn-dialog pa-3 pa-sm-11">
            <!-- 👉 dialog close btn -->
            <DialogCloseBtn variant="text" size="default" @click="emit('update:isDialogVisible', false)" />

            <VCardText class="pa-5">
                <div class="mb-6">
                    <h4 class="text-h4 text-center mb-2" v-if="props.petSelected">
                        Eliminar Mascota: {{ props.petSelected.name }}
                    </h4>
                </div>
                <p v-if="props.petSelected">
                    ¿Estás seguro de que deseas eliminar la mascota "{{ props.petSelected.name }}"? Esta acción no se puede deshacer.
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
