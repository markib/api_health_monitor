<template>
  <div>
    <label for="clientSelect" class="block mb-2 font-semibold">Select Client Email:</label>
    <select id="clientSelect" v-model="selectedClientId" @change="fetchEndpoints" class="mb-4 p-2 border rounded w-full max-w-sm">
      <option value="" disabled>Select a client</option>
      <option v-for="client in clients" :key="client.id" :value="client.id">
        {{ client.email }}
      </option>
    </select>

    <ul v-if="endpoints.length">
      <li v-for="endpoint in endpoints" :key="endpoint.id" class="mb-2">
        <a href="#" @click.prevent="confirmVisit(endpoint.url)" class="text-blue-600 underline cursor-pointer">
          {{ endpoint.url }}
        </a>
      </li>
    </ul>

    <!-- Confirmation Dialog -->
    <div v-if="showDialog" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded shadow max-w-sm w-full">
        <p class="mb-4">You are about to visit <strong>{{ dialogUrl }}</strong>. Proceed?</p>
        <div class="flex justify-end gap-4">
          <button @click="proceed" class="bg-blue-600 text-white px-4 py-2 rounded">Proceed</button>
          <button @click="cancel" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
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
/* Add any custom styles here */
</style>
