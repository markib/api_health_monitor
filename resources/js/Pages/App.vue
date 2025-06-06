<template>
  <div>
    <ClientForm />
    <ClientEndpoint :clients="clients" />
  </div>
</template>

<script setup>
import ClientEndpoint from '@/Components/ClientEndpoints.vue'
import ClientForm from '@/Components/Clientform.vue'
defineProps({ clients: Array })
</script>
