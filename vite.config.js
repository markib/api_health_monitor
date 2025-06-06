import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'; // Keep this if you're using it
import vue from '@vitejs/plugin-vue'; // Import the Vue plugin

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // Configure the Vue plugin to correctly transform asset URLs
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(), // Ensure this is correctly configured if it's a custom plugin
    ],
    server: {
        // This helps resolve potential IPv6 issues ([::1]) or general host problems
        // and ensures HMR works correctly.
        host: '127.0.0.1', // Or '0.0.0.0' if you're in a VM/Docker and need external access
        port: 5173, // Make sure this matches the port Vite is trying to use
        hmr: {
            host: '127.0.0.1', // Should match the main host
        },
    },
});