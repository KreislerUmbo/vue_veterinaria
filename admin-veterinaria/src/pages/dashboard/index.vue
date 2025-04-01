<template>
  <div>
    <VCardText>
      <h4 class="text-h4 mb-1">
        <span> Bienvenido al Dashboard</span> comienza la aventura
      </h4>

      <p class="mb-0">
        Inicie sesión en su cuenta y comienza la aventura
      </p>
    </VCardText>
  </div>
</template>
<script setup>
definePage({
  meta: {
    permisssion: 'all',
  },
})
</script>