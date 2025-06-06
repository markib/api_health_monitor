<script setup>
// This component typically doesn't need much script logic itself,
// as the main page content (like Dashboard.vue) will handle its own logic.
// It mainly provides a structural wrapper.
</script>

<template>
  <div class="min-h-screen bg-gray-100 antialiased font-inter text-gray-900">
    <!-- You can place global elements here, like a navigation bar -->
    <nav class="bg-white shadow-sm py-4">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-10">
          <div class="flex-shrink-0">
            <a href="/" class="text-2xl font-bold text-blue-600">
              API Monitor
            </a>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <!-- Example Navigation Links -->
            <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
            <!-- Add other links as needed -->
          </div>
        </div>
      </div>
    </nav>

    <!--
      The <slot /> is where your actual Inertia page components (like Dashboard.vue)
      will be rendered by Inertia.js.
      This allows App.vue to act as a consistent layout wrapper for all your pages.
    -->
    <main>
      <slot />
    </main>

    <!-- You can place a global footer here -->
    <footer class="bg-gray-800 text-white py-6 mt-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
        &copy; {{ new Date().getFullYear() }} API Health Monitor. All rights reserved.
      </div>
    </footer>
  </div>
</template>

