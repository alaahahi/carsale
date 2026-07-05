<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const page = usePage();

const loginBgStyle = computed(() => {
  const url = page.props.systemConfig?.login_bg_url || '/img/logo-color1.png';
  return {
    backgroundImage: `url('${url}')`,
    backgroundSize: 'cover',
  };
});
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100" :style="loginBgStyle">
        <div>
    
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
