<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <Head title="API Health Monitor Dashboard" />

    <header class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-8">
      <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">API Health Monitoring Dashboard</h1>
      <p class="mt-2 text-lg text-gray-600">Add new clients or view existing endpoints.</p>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
      <!-- Client Form Section -->
      <section class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Add New Client</h2>
        <!--
          ClientForm is used here.
          It emits 'clientSubmitted' when a new client is successfully added.
          We listen for this event and call handleClientSubmitted to refresh the client list.
        -->
        <ClientForm @clientSubmitted="handleClientSubmitted" />
      </section>

      <!-- Client Endpoints Display Section -->
      <section class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">View Existing Clients</h2>
        <!--
          ClientEndpoint component receives the 'clients' data fetched by this Dashboard.
          This ensures the dropdown has the most up-to-date list of clients.
        -->
        <ClientEndpoints :clients="clients" />
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientEndpoints from '@/Components/ClientEndpoints.vue';
import ClientForm from '@/Components/ClientForm.vue'; // Adjust path if needed

// Reactive state to hold the list of clients fetched from the API
const clients = ref([]);

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
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Parse the JSON response
        const data = await response.json();
        // Update the reactive 'clients' ref with the fetched data
        clients.value = data;
    } catch (error) {
        console.error('Failed to fetch clients:', error);
        // TODO: Implement user-friendly error display (e.g., a banner or message)
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

