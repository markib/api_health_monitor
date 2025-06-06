import axios from 'axios';
window.axios = axios;

// Sets a common header for all Axios requests, identifying them as AJAX requests.
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Ensures that cookies (like session cookies and CSRF tokens) are sent with cross-site requests.
window.axios.defaults.withCredentials = true;

// Automatically includes the X-XSRF-TOKEN header with requests, essential for Laravel's CSRF protection.
// This relies on Laravel's built-in XSRF-TOKEN cookie.
window.axios.defaults.withXSRFToken = true;

// Sets the base URL for all Axios requests.
// It uses the VITE_APP_URL environment variable (from your .env file)
// and appends '/api' to it, directing requests to your Laravel API endpoints.
window.axios.defaults.baseURL = `${import.meta.env.VITE_APP_URL}/api`;