<template>
  <div class="p-4 max-w-xl mx-auto">
    <label for="clientSelect" class="block mb-2 font-semibold text-gray-700">Select Client Email:</label>
    <select
      id="clientSelect"
      v-model="selectedClientId"
      @change="fetchEndpoints"
      class="mb-6 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full bg-white"
    >
      <option value="" disabled>Select a client</option>
      <option v-for="client in clients" :key="client.id" :value="client.id">
        {{ client.email }}
      </option>
    </select>

    <ul v-if="endpoints.length" class="space-y-3">
      <li
        v-for="endpoint in endpoints"
        :key="endpoint.id"
        class="bg-gray-50 border border-gray-200 p-4 rounded-lg hover:bg-blue-50 transition"
      >
        <a
          href="#"
          @click.prevent="confirmVisit(endpoint.url)"
          class="text-blue-600 hover:text-blue-800 underline font-medium"
        >
          {{ endpoint.url }}
        </a>
      </li>
    </ul>

    <!-- Confirmation Dialog -->
    <div
      v-if="showDialog"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
      <div class="bg-white p-6 rounded-xl shadow-xl max-w-sm w-full mx-4">
        <p class="mb-4 text-gray-800">
          You are about to visit
          <strong class="text-blue-700">{{ dialogUrl }}</strong>. Proceed?
        </p>
        <div class="flex justify-end gap-4">
          <button
            @click="proceed"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
          >
            Proceed
          </button>
          <button
            @click="cancel"
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ClientEndpointViewer',
  data() {
    return {
      clients: [],
      selectedClientId: '',
      endpoints: [],
      showDialog: false,
      dialogUrl: '',
    };
  },
  mounted() {
    this.fetchClients();
  },
  methods: {
    async fetchClients() {
      try {
        const res = await axios.get('/api/clients');
        this.clients = res.data;
      } catch (error) {
        console.error('Failed to fetch clients:', error);
      }
    },
    async fetchEndpoints() {
      if (!this.selectedClientId) {
        this.endpoints = [];
        return;
      }
      try {
        const res = await axios.get(`/api/clients/${this.selectedClientId}`);
        this.endpoints = res.data;
      } catch (error) {
        console.error('Failed to fetch endpoints:', error);
        this.endpoints = [];
      }
    },
    confirmVisit(url) {
      this.dialogUrl = url;
      this.showDialog = true;
    },
    proceed() {
      window.open(this.dialogUrl, '_blank');
      this.showDialog = false;
      this.dialogUrl = '';
    },
    cancel() {
      this.showDialog = false;
      this.dialogUrl = '';
    },
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
