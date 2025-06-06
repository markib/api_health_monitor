<template>
  <div>
    
    <ClientEndpoint :clients="clients" />
  </div>
</template>

<script setup>
import ClientEndpoint from '@/Components/ClientEndpoints.vue'
defineProps({ clients: Array })
</script>
