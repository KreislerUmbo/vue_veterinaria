<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components/VForm'
import { useCalendarStore } from '@/plugins/calendar/useCalendarStore'
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar5 from '@images/avatars/avatar-5.png'
import avatar6 from '@images/avatars/avatar-6.png'
import avatar7 from '@images/avatars/avatar-7.png'
import { VSelect } from 'vuetify/components'
import { ref, watch } from 'vue'

// 👉 store
const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  event: {
    type: null,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'addEvent',
  'updateEvent',
  'removeEvent',
])

const store = useCalendarStore()
const refForm = ref()

// 👉 Event
const event = ref(JSON.parse(JSON.stringify(props.event)))
const state =ref(1);
const resetEvent = () => {
  event.value = JSON.parse(JSON.stringify(props.event))
  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

watch(event,(new_event)=>{ state.value=new_event.extendedProps.state;});// observar cambios en el evento para actualizar el estado
watch(() => props.isDrawerOpen, resetEvent)

const removeEvent = () => {
  emit('removeEvent', String(event.value.id))

  // Close drawer
  emit('update:isDrawerOpen', false)
}

const handleSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {

      // If id exist on id => Update event
      if ('id' in event.value)
        emit('updateEvent', event.value)

      // Else => add new event
      else
        emit('addEvent', event.value)

      // Close drawer
      emit('update:isDrawerOpen', false)
    }
  })
}

const guestsOptions = [
  {
    avatar: avatar1,
    name: 'Jane Foster',
  },
  {
    avatar: avatar3,
    name: 'Donna Frank',
  },
  {
    avatar: avatar5,
    name: 'Gabrielle Robertson',
  },
  {
    avatar: avatar7,
    name: 'Lori Spears',
  },
  {
    avatar: avatar6,
    name: 'Sandy Vega',
  },
  {
    avatar: avatar2,
    name: 'Cheryl May',
  },
]

// 👉 Form
const onCancel = () => {

  // Close drawer
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    resetEvent()
    refForm.value?.resetValidation()
  })
}

const startDateTimePickerConfig = computed(() => {
  const config = {
    enableTime: !event.value.allDay,
    dateFormat: `Y-m-d${ event.value.allDay ? '' : ' H:i' }`,
  }

  if (event.value.end)
    config.maxDate = event.value.end
  
  return config
})

const endDateTimePickerConfig = computed(() => ({
  enableTime: !event.value.allDay,
  dateFormat: `Y-m-d${ event.value.allDay ? '' : ' H:i' }`,
  minDate: event.value.start || undefined,
}))

const dialogModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="420"
    class="scrollable-content"
    border="none"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- 👉 Header -->
    <AppDrawerHeaderSection
      :title="event.id ? 'Actualizar Cita' : 'Add Event'"
      @cancel="$emit('update:isDrawerOpen', false)"
    >
      <template #beforeClose>
        <IconBtn
          v-show="event.id"
          size="large"
          class="text-medium-emphasis"
          @click="removeEvent"
        >
          <VIcon icon="ri-delete-bin-7-line" />
        </IconBtn>
      </template>
    </AppDrawerHeaderSection>

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- SECTION Form -->
          <VForm
            ref="refForm"
            @submit.prevent="handleSubmit"
          >
            <VRow>
              <!-- 👉 Mascota -->
              <VCol cols="12">
                <VTextField
                  v-model="event.extendedProps.pet.name"
                  label="Mascota"
                  placeholder=""
                  :rules="[requiredValidator]" readonly
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="event.extendedProps.veterinarie.full_name"
                  label="Veterinario"
                  placeholder="Nombre del Veterinario"
                  readonly 
                />
              </VCol>
            <!-- 👉 Nombre del día de la cita -->
                <VCol cols="6">
                 <VTextField
                  v-model="event.extendedProps.day"
                  label="Día"
                  placeholder="Día de la cita"
                  readonly
                />
               </VCol>
              <!-- 👉 Start date -->
              <VCol cols="6">
                <AppDateTimePicker
                  :key="JSON.stringify(startDateTimePickerConfig)"
                  v-model="event.start"
                  :rules="[requiredValidator]"
                  label="Hora de Atención"
                  placeholder="Select Date"
                  :config="startDateTimePickerConfig"
                  readonly
                />
              </VCol>
               <VCol cols="12">
                <VTextField
                  v-model="event.extendedProps.amount"
                  label="Monto"
                  placeholder="Monto de la cita"
                  type="number"
                  readonly
                />
               </VCol>

              <!-- 👉 State de la cita -->
              <VCol cols="12">
                <VSelect
                :items="[
                  {name: 'Pendiente',id:1,},
                  {name: 'Cancelado',id:2,},
                  {name: 'Atendido',id:3,}                                
                ]"
                v-model="event.extendedProps.state"
                :disabled="state == 2 || state == 3 ? true : false"
                label="Estado de la cita"
                item-title="name"
                item-value="id"
                placeholder="Selec. un Estado"
                eager=""
                />
              </VCol>

              <!-- 👉 Razon de la cita -->
              <VCol cols="12">
                <VTextarea
                  v-model="event.extendedProps.description"
                  label="Razón:"
                  placeholder="Razón de la cita médica"
                  readonly
                />
              </VCol>
              <!-- 👉 Nota-->
              <VCol cols="12">
                <VTextarea
                  v-model="event.extendedProps.notes"
                  label="Nota Médica de la Mascota:"
                  placeholder="Alguna nota adicional"
                />
              </VCol>

              <!-- 👉 Form buttons -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Actualizar
                </VBtn>
                <VBtn
                  variant="outlined"
                  color="secondary"
                  @click="onCancel"
                >
                  Listado
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        <!-- !SECTION -->
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
