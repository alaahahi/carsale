<template>
  <button @click="toggleDarkMode" class="px-2 py-1 rounded-full focus:outline-none dark:text-gray-400">
  <svg v-if="!darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
  </svg>
  <svg v-if="darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
  </svg>

  </button>
</template>

<script>
export default {
  data() {
    return {
      darkMode: false,
    };
  },
  created() {
    // Retrieve dark mode status from local storage on component creation
    this.darkMode = localStorage.getItem('darkMode') === 'true';
    // Apply dark mode class to the HTML element if dark mode is enabled
    if (this.darkMode) {
      document.documentElement.classList.add('dark');
    }
  },
  methods: {
    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      if (this.darkMode) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
      // Store dark mode status in local storage
      localStorage.setItem('darkMode', this.darkMode);
    },
  },
  computed: {
    iconClass() {
      return this.darkMode ? 'fas fa-sun' : 'fas fa-moon';
    },
  },
};
</script>