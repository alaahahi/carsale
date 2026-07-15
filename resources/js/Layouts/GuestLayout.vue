<script setup>
</script>

<template>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
        :style="{
            backgroundImage: `url('${$page.props.systemConfig?.login_bg_url || '/img/logo-color1.png'}')`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        }"
    >
        <div></div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg dark:bg-gray-800">
            <slot />
        </div>
    </div>
</template>
