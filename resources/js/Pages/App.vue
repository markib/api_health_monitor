<template>
  <div>
    <h1 class="text-xl font-bold mb-4">API Health Monitor</h1>
    <ClientEndpoint :clients="clients" />
  </div>
</template>

<script setup>
import ClientEndpoint from '@/Components/ClientEndpoints.vue'
defineProps({ clients: Array })
</script>
