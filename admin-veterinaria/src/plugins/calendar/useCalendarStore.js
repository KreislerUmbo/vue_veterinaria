export const useCalendarStore = defineStore('calendar', {
  // arrow function recommended for full type inference
  state: () => ({
    availableCalendars: [
      {
        color: 'error',
        label: 'Personal',
      },
      {
        color: 'primary',
        label: 'Appointment', //color morado para citas 
      },
      {
        color: 'warning',
        label: 'Vaccination',//color naranja para vacunacion
      },
      {
        color: 'success',
        label: 'Surgeries', //color verde para cirugias
      },
      {
        color: 'info',
        label: 'ETC',
      },
    ],
    selectedCalendars: ['Personal', 'Appointment', 'Vaccination', 'Surgeries', 'ETC'],
  }),
  actions: {
    async fetchEvents() {
      const { data, error } = await useApi(createUrl('/medical-records/calendar', ))

      if (error.value)
        return error.value
      console.log(data)
      return data.value.calendars.data;
    },
    async addEvent(event) {
      await $api('/apps/calendar', {
        method: 'POST',
        body: event,
      })
    },
    async updateEvent(event) {
      return await $api(`/medical-records/update_aux/${event.id}`, {
        method: 'PUT',
        body: {// enviando solo los campos que se pueden actualizar desde el calendario
          state: event.extendedProps.state,//
          notes: event.extendedProps.notes,
        },
      })
    },
    async removeEvent(eventId) {
      return await $api(`/apps/calendar/${eventId}`, {
        method: 'DELETE',
      })
    },
  },
})
