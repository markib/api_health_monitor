<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';

// Define emitted events: 'clientSubmitted' will be emitted when a client is successfully added
const emit = defineEmits(['clientSubmitted']);

// Form data reactive object using Inertia's useForm
const form = useForm({
    email: '',
    endpoints: '',
});

// State for managing messages to the user
const message = ref('');
// State for message type (success or error)
const messageType = ref('');

/**
 * Displays a message to the user in a styled box.
 * @param {string} msg The message to display.
 * @param {string} type The type of message ('success' or 'error').
 */
const showMessage = (msg, type = 'success') => {
    message.value = msg;
    messageType.value = type;
    // Automatically hide the message after 5 seconds
    setTimeout(() => {
        message.value = '';
        messageType.value = '';
    }, 5000);
};

/**
 * Handles the form submission using Inertia's post method.
 * Inertia automatically handles form data, loading states, and redirects/errors.
 */
const submitForm = () => {
    // Basic client-side validation before sending
    if (!form.email.trim() || !form.endpoints.trim()) {
        showMessage('Please fill in both email and endpoints fields.', 'error');
        return;
    }

    // Transform endpoints string into an array for submission
    // Inertia's useForm handles the actual POST request
    // This assumes you have a Laravel POST route at '/api/clients'
    // that handles storing the client and invalidates the Redis cache.
    form.post('/api/clients', { // POSTs to the /api/clients route managed by ClientSubmissionController
        preserveScroll: true, // Keep scroll position after submission
        onSuccess: () => {
            showMessage('Form submitted successfully! Your data has been received.', 'success');
            // Clear form fields after successful submission
            form.reset('email', 'endpoints');
            message.value = ''; // Clear message after success
            // Emit an event to the parent component to signal that a new client was submitted
            emit('clientSubmitted');
        },
        onError: (errors) => {
            // Inertia automatically populates form.errors with validation errors
            console.error('Submission failed:', errors);
            if (errors && Object.keys(errors).length > 0) {
                // Display the first error message or a generic one
                // You might iterate over errors for more detailed display
                showMessage(`Submission failed: ${Object.values(errors)[0]}`, 'error');
            } else {
                showMessage('An unknown error occurred during submission.', 'error');
            }
        },
        onFinish: () => {
            // This runs whether success or error
            // Loading state is automatically handled by form.processing
        },
    });
};
</script>

<template>
    <!-- Inertia Head component for setting page title -->
    <Head title="Client Form" />

    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6 lg:p-8 bg-gray-100 font-inter">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-xl w-full max-w-md border border-gray-200">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Client Information Form</h2>

            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Email Address Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="name@example.com"
                        required
                        v-model="form.email"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out"
                    />
                    <!-- Display validation error for email if it exists -->
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- Endpoints Textarea -->
                <div>
                    <label for="endpoints" class="block text-sm font-medium text-gray-700 mb-1">List Endpoints (one per line)</label>
                    <textarea
                        id="endpoints"
                        name="endpoints"
                        rows="5"
                        placeholder="e.g.,&#10;/api/users&#10;/api/products&#10;/api/orders/{id}"
                        required
                        v-model="form.endpoints"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm resize-y transition duration-150 ease-in-out"
                    ></textarea>
                    <!-- Display validation error for endpoints if it exists -->
                    <div v-if="form.errors.endpoints" class="text-red-600 text-xs mt-1">{{ form.errors.endpoints }}</div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white"
                    :class="{
                        'bg-blue-400 cursor-not-allowed': form.processing,
                        'bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out transform hover:scale-105': !form.processing
                    }"
                >
                    <template v-if="form.processing">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template v-else>
                        Submit
                    </template>
                </button>
            </form>

            <!-- Message Box for Feedback -->
            <div
                v-if="message"
                id="message-box"
                class="mt-6 p-3 rounded-md text-sm"
                :class="{
                    'bg-green-100 border border-green-400 text-green-700': messageType === 'success',
                    'bg-red-100 border border-red-400 text-red-700': messageType === 'error'
                }"
                role="alert"
            >
                {{ message }}
            </div>
        </div>
    </div>
</template>

