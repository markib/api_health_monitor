<template>
         <div class="container mx-auto p-4">
             <h1 class="text-2xl mb-4">API Health Monitoring</h1>
             <select v-model="selectedClient" @change="fetchEndpoints" class="border p-2 mb-4 w-full">
                 <option value="">Select a client</option>
                 <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.email }}</option>
             </select>
             <div v-if="loading" class="text-center">Loading...</div>
             <div v-else-if="endpoints.length">
                 <h2 class="text-xl mb-2">Endpoints</h2>
                 <table class="w-full border-collapse">
                     <thead>
                         <tr class="bg-gray-200">
                             <th class="border p-2">URL</th>
                             <th class="border p-2">Status</th>
                             <th class="border p-2">Last Checked</th>
                             <th class="border p-2">Response Time</th>
                             <th class="border p-2">Details</th>
                         </tr>
                     </thead>
                     <tbody>
                         <tr v-for="endpoint in endpoints" :key="endpoint.id">
                             <td class="border p-2">
                                 <a href="#" @click.prevent="confirmVisit(endpoint.url)">{{ endpoint.url }}</a>
                             </td>
                             <td class="border p-2" :class="endpoint.latest_result?.is_healthy ? 'text-green-500' : 'text-red-500'">
                                 {{ endpoint.latest_result?.is_healthy ? 'Healthy' : 'Unhealthy' }}
                             </td>
                             <td class="border p-2">{{ endpoint.latest_result?.human_time || 'N/A' }}</td>
                             <td class="border p-2">{{ endpoint.latest_result?.response_time_ms ? endpoint.latest_result.response_time_ms + ' ms' : 'N/A' }}</td>
                             <td class="border p-2">{{ endpoint.latest_result?.error_message || 'N/A' }}</td>
                         </tr>
                     </tbody>
                 </table>
             </div>
             <p v-else-if="selectedClient">No endpoints found.</p>

             <dialog ref="confirmDialog" class="p-4 rounded">
                 <p>You are about to visit <span class="font-bold">{{ dialogUrl }}</span>. Proceed?</p>
                 <div class="mt-4 flex justify-end gap-2">
                     <button @click="proceed" class="bg-blue-500 text-white px-4 py-2 rounded">Proceed</button>
                     <button @click="cancel" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                 </div>
             </dialog>
         </div>
     </template>

     <script setup>
     import { ref, onMounted } from 'vue';

     const clients = ref([]);
     const selectedClient = ref('');
     const endpoints = ref([]);
     const loading = ref(false);
     const dialogUrl = ref('');
     const confirmDialog = ref(null);

     onMounted(() => {
         fetchClients();
     });

     async function fetchClients() {
         try {
             const response = await fetch('/api/clients');
             if (!response.ok) throw new Error(`HTTP ${response.status}`);
             clients.value = await response.json();
         } catch (error) {
             console.error('Error fetching clients:', error);
         }
     }

     async function fetchEndpoints() {
         if (!selectedClient.value) {
             endpoints.value = [];
             return;
         }
         loading.value = true;
         try {
             const response = await fetch(`/api/clients/${selectedClient.value}`);
             if (!response.ok) throw new Error(`HTTP ${response.status}`);
             endpoints.value = await response.json();
         } catch (error) {
             console.error('Error fetching endpoints:', error);
             endpoints.value = [];
         } finally {
             loading.value = false;
         }
     }

     function confirmVisit(url) {
         dialogUrl.value = url;
         confirmDialog.value.showModal();
     }

     function proceed() {
         window.open(dialogUrl.value, '_blank');
         cancel();
     }

     function cancel() {
         confirmDialog.value.close();
         dialogUrl.value = '';
     }
     </script>

     <style scoped>
     dialog {
         border: none;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
     }
     table {
         border: 1px solid #e2e8f0;
     }
     th, td {
         border: 1px solid #e2e8f0;
         text-align: left;
     }
     </style>