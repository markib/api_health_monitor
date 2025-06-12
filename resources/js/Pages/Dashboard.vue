<template>
  <div class="min-h-screen bg-gray-100">
    <Head title="API Health Monitor Dashboard" />
    <header class="max-w-4xl mx-auto px-4 py-4 text-center">
      <h1 class="text-2xl font-bold text-gray-900">API Health Monitoring Dashboard</h1>
      <p class="mt-1 text-sm text-gray-600">Add new clients or view existing endpoints.</p>
    </header>
    <main class="max-w-4xl mx-auto px-4 space-y-4">
      <div v-if="message" class="p-2 rounded-md text-sm" :class="{ 'bg-green-100 text-green-700': messageType === 'success', 'bg-red-100 text-red-700': messageType === 'error' }">
        {{ message }}
      </div>
      <section class="bg-white p-4 rounded-lg shadow-md border">
        <h2 class="text-xl font-bold text-gray-800 mb-2">Add New Client</h2>
        <ClientForm @clientSubmitted="handleClientSubmitted" />
      </section>
      <section class="bg-white p-4 rounded-lg shadow-md border">
        <h2 class="text-xl font-bold text-gray-800 mb-2">View Existing Clients</h2>
        <ClientEndpoints :clients="clients" />
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientEndpoints from '@/Components/ClientEndpoints.vue';
import ClientForm from '@/Components/ClientForm.vue'; 

// Reactive state to hold the list of clients fetched from the API
const clients = ref([]);

// --- New state for messages within Dashboard.vue ---
const message = ref('');
const messageType = ref('');

/**
 * Displays a message to the user in a styled box for the dashboard.
 * @param {string} msg The message to display.
 * @param {string} type The type of message ('success' or 'error').
 */
const showMessage = (msg, type = 'error') => { // Default to error for dashboard messages
    message.value = msg;
    messageType.value = type;
    // Automatically hide the message after 5 seconds
    setTimeout(() => {
        message.value = '';
        messageType.value = '';
    }, 5000);
};


/**
 * Fetches the list of clients (emails and IDs) from the Laravel API endpoint.
 * This function is asynchronous as it makes a network request.
 * It's crucial for populating the dropdown in ClientEndpoints.
 */
const fetchClients = async () => {
    try {
        // Make a GET request to your Laravel API's index method for clients
        // This corresponds to the ClientController@index method you provided.
        const response = await fetch('/api/clients');

        // Check if the network response was successful
        if (!response.ok) {
              // Parse error response if available for more details
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(`HTTP error! status: ${response.status} - ${errorData.message || 'Server error'}`);
        }

        // Parse the JSON response
        const data = await response.json();
        // Update the reactive 'clients' ref with the fetched data
        clients.value = data;
         showMessage('Clients loaded successfully!', 'success');
    } catch (error) {
        console.error('Failed to fetch clients:', error);
        // TODO: Implement user-friendly error display (e.g., a banner or message)
        showMessage(`Failed to load clients: ${error.message}`, 'error');
    }
};

/**
 * Callback function executed when the ClientForm successfully submits a new client.
 * This triggers a refetch of the client list to ensure the ClientEndpoint component
 * displays the most up-to-date information, reflecting the newly added client.
 */
const handleClientSubmitted = () => {
    console.log('New client submitted, refetching client list...');
    fetchClients(); // Call fetchClients again to refresh the data
};

// When the Dashboard component is mounted to the DOM, fetch the initial list of clients
onMounted(() => {
    fetchClients();
});
</script>

