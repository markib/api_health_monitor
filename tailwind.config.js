// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php', // Your blade views
        './resources/js/**/*.vue',          // Crucial for Vue components
        './resources/js/**/*.js'
    ],
    theme: {
        extend: {
            // This is where you define your custom fonts
            fontFamily: {
                // Ensure 'Inter' is included here if you want to use it as a custom font
                sans: ['Inter', 'sans-serif'],
                // If you want to replace the default sans font, you can do:
                // sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
            },
        },
    },
    plugins: [],
  };