<script setup>
import { ref, watch } from 'vue';

// Define props: clients array (from parent) and an optional selectedClientId (for initial selection)
const props = defineProps({
    clients: {
        type: Array,
        default: () => []
    }
});

// Reactive state for the currently selected client's ID
const selectedClientId = ref(null);
// Reactive state for the endpoints of the selected client
const selectedClientEndpoints = ref([]);
// Reactive state for managing the confirmation dialog visibility
const showConfirmDialog = ref(false);
// Reactive state to store the URL being confirmed
const urlToOpen = ref('');
// --- New state for messages within ClientEndpoints.vue ---
const message = ref('');
const messageType = ref('');

/**
 * Fetches endpoints for a specific client ID from the API.
 * This function will be called when a client is selected.
 * @param {number} clientId The ID of the client whose endpoints to fetch.
 */
const fetchEndpointsForClient = async (clientId) => {
    if (!clientId) {
        selectedClientEndpoints.value = [];
        return;
    }
    try {
        // Fetch endpoints from the Laravel API's show method
        const response = await fetch(`/api/clients/${clientId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        // Update the reactive state with the fetched endpoints
        selectedClientEndpoints.value = data;
    } catch (error) {
        console.error(`Failed to fetch endpoints for client ${clientId}:`, error);
        selectedClientEndpoints.value = []; // Clear endpoints on error
        // Optionally, display an error message to the user
    }
};

// Watch for changes in selectedClientId and fetch endpoints accordingly
watch(selectedClientId, (newId) => {
    fetchEndpointsForClient(newId);
});

/**
 * Handles the change event of the client dropdown.
 * Updates the selectedClientId state.
 * @param {Event} event The change event from the select element.
 */
const onClientSelectChange = (event) => {
    selectedClientId.value = event.target.value;
};

/**
 * Shows the confirmation dialog before opening an endpoint URL.
 * @param {string} url The URL of the endpoint to potentially open.
 */
const confirmVisit = (url) => {
    urlToOpen.value = url;
    showConfirmDialog.value = true;
};

/**
 * Proceeds to open the URL in a new tab.
 */
const proceedToVisit = () => {
    if (urlToOpen.value) {
        window.open(urlToOpen.value, '_blank');
    }
    showConfirmDialog.value = false;
    urlToOpen.value = '';
};

/**
 * Cancels the visit and closes the dialog.
 */
const cancelVisit = () => {
    showConfirmDialog.value = false;
    urlToOpen.value = '';
};

const monitorApiHealth = async () => {
    if (!selectedClientId.value) {
        return;
    }

    try {
        
        const response = await axios.post(`/clients/${selectedClientId.value}/monitor`);
          // Check if the response was successful and contains the message
        if (response.data && response.data.message) {
            showMessage(response.data.message, 'success');
        } else {
            showMessage('Monitoring job dispatched (no specific message from server).', 'success');
        }
    } catch (error) {
        console.error('Health check failed', error);
        let errorMessage = 'Failed to dispatch health check.';
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = `Health check failed: ${error.response.data.message}`;
        }
        showMessage(errorMessage, 'error');
    }
};

const showMessage = (msg, type = 'success') => {
    message.value = msg;
    messageType.value = type;
    // THIS setTimeout SHOULD BE ACTIVE to hide the message after a delay
    setTimeout(() => {
        message.value = '';
        messageType.value = '';
    }, 5000); // Message will hide after 5 seconds
};
</script>

<template>
    <div class="max-w-xl mx-auto mt-8 bg-white p-6 rounded-lg shadow-xl border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Clients and Endpoints</h2>

        <!-- Client Email Dropdown -->
        <div class="mb-6">
            <label for="client-select" class="block text-sm font-medium text-gray-700 mb-1">Select Client Email</label>
            <select
                id="client-select"
                v-model="selectedClientId"
                @change="onClientSelectChange"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
            >
                <option :value="null" disabled>-- Select a client --</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.email }}
                </option>
            </select>
        </div>

        <!-- Display Endpoints for Selected Client -->
        <div v-if="selectedClientEndpoints.length > 0">
    <h3 class="text-lg font-medium text-gray-700 mb-3">
    Endpoints ({{ selectedClientEndpoints.length }})
</h3>
    <ul class="space-y-4">
        <li
            v-for="endpoint in selectedClientEndpoints"
            :key="endpoint.id"
            class="bg-white p-4 rounded-lg shadow-sm border border-gray-200"
        >
            <div class="text-blue-600 font-semibold">
                <a
                    href="#"
                    @click.prevent="confirmVisit(endpoint.url)"
                    class="hover:text-blue-800 hover:underline"
                >
                    {{ endpoint.url }}
                </a>
            </div>

            <div class="text-sm text-gray-700 mt-2">
                <p>
                    <strong>Status:</strong>
                    <span
                        :class="{
                            'text-green-600': endpoint.latest_result?.is_healthy,
                            'text-red-600': !endpoint.latest_result?.is_healthy
                        }"
                    >
                        {{ endpoint.latest_result?.is_healthy ? 'Healthy' : 'Unhealthy' }}
                    </span>
                </p>
                <p><strong>Checked At:</strong> {{ endpoint.latest_result?.checked_at ?? 'N/A' }}</p>
                <p><strong>Response Time:</strong> {{ endpoint.latest_result?.response_time_ms ?? 'N/A' }} ms</p>
                <p v-if="endpoint.latest_result?.error_message">
                    <strong>Error:</strong> {{ endpoint.latest_result.error_message }}
                </p>
            </div>
        </li>
    </ul>

    <button
    v-if="selectedClientEndpoints.length > 0"
    @click="monitorApiHealth"
    class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
>
    Monitor API Health
</button>

</div>

        <div v-else-if="selectedClientId" class="text-gray-500">
            No endpoints found for the selected client, or failed to load.
        </div>
        <div v-else class="text-gray-500">
            Select a client to view their endpoints.
        </div>

        <!-- Confirmation Dialog (Modal) -->
        <div v-if="showConfirmDialog" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Confirm Navigation</h3>
                <p class="text-gray-700 mb-6">You are about to visit: <span class="font-semibold break-all">{{ urlToOpen }}</span>. Proceed?</p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="cancelVisit"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150"
                    >
                        Cancel
                    </button>
                    <button
                        @click="proceedToVisit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150"
                    >
                        Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

