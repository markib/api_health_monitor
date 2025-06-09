<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import axios from 'axios';

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
    // THIS setTimeout SHOULD BE ACTIVE to hide the message after a delay
    setTimeout(() => {
        message.value = '';
        messageType.value = '';
    }, 5000); // Message will hide after 5 seconds
};

/**
 * Handles the form submission using Inertia's post method.
 * Inertia automatically handles form data, loading states, and redirects/errors.
 */
const submitForm = async() => {
    // Basic client-side validation before sending
    if (!form.email.trim() || !form.endpoints.trim()) {
        showMessage('Please fill in both email and endpoints fields.', 'error');
        return;
    }

    const endpointsArray = form.endpoints
        .split('\n')
        .map((e) => e.trim())
        .filter((e) => e !== '');

      try {
        const response = await axios.post('/clients', {
            email: form.email,
            endpoints: endpointsArray,
        });

        console.log('Server response:', response.data);

        showMessage('Form submitted successfully! Your data has been received.', 'success');

        // Optionally reset
        // form.reset('email', 'endpoints');
        // emit('clientSubmitted');
    } catch (error) {
        console.error('Submission error:', error.response?.data || error.message);
        const msg = error.response?.data?.message || 'Something went wrong.';
        showMessage(`Submission failed: ${msg}`, 'error');
    }
};
</script>

<template>
    <!-- Inertia Head component for setting page title -->
    <Head title="Client Form" />

    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6 lg:p-8 bg-gray-100 font-inter">
        <div class="bg-white p-6 sm:p-8 rounded-lg shadow-xl w-full max-w-md border border-gray-200 form-container">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6 form-title">Client Information Form</h2>

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
                    <div v-if="form.errors.email" class="text-red-600 text-xs mt-1 error-message">{{ form.errors.email }}</div>
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
                    <div v-if="form.errors.endpoints" class="text-red-600 text-xs mt-1 error-message">{{ form.errors.endpoints }}</div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white submit-button"
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
                class="mt-6 p-3 rounded-md text-sm message-box"
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

<style>
/* Custom font for better aesthetics */
body {
    font-family: "Inter", sans-serif;
}

/* Enhancements for the form container */
.form-container {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease-in-out;
}

.form-container:hover {
    transform: translateY(-5px);
}

/* Styling for the form title */
.form-title {
    color: #333; /* Darker gray for better contrast */
    text-shadow: 1px 1px 2px rgba(0,0,0,0.05); /* Subtle text shadow */
}

/* Additional styling for input fields (though Tailwind handles most) */
input[type="email"],
textarea {
    transition: all 0.2s ease-in-out;
}

input[type="email"]:focus,
textarea:focus {
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); /* Blue glow on focus */
    border-color: #3b82f6; /* Blue border on focus */
}

/* Submit button enhancements */
.submit-button {
    background: linear-gradient(to right, #3b82f6, #2563eb); /* Gradient background */
    border: none; /* Remove default border */
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.submit-button:hover {
    background: linear-gradient(to right, #2563eb, #1e40af); /* Darker gradient on hover */
}

.submit-button:active {
    transform: translateY(1px); /* Slight press effect */
}

/* Subtle animation for the message box */
.message-box {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Styling for validation error messages */
.error-message {
    font-size: 0.75rem; /* Smaller font size */
    color: #dc2626; /* Red color */
    margin-top: 0.25rem; /* Small margin-top */
}
</style>
