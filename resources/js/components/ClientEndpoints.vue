<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl mb-4">API Health Monitoring</h1>
        <select v-model="selectedClient" @change="fetchEndpoints" class="border p-2 mb-4 w-full">
            <option value="">Select a client</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.email }}</option>
        </select>
        <ul v-if="endpoints.length" class="list-disc pl-5">
            <li v-for="endpoint in endpoints" :key="endpoint.id">
                <a href="#" @click.prevent="confirmVisit(endpoint.url)">{{ endpoint.url }}</a>
            </li>
        </ul>
        <p v-else-if="selectedClient && !loading">No endpoints found.</p>
        <p v-if="loading">Loading...</p>

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
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${await response.text()}`);
        }
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
        if (!response.ok) {
            const text = await response.text();
            console.error('Fetch endpoints failed: HTTP ${response.status}, Response: ${text}');
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
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
</style>