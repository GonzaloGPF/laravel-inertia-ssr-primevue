<script setup>
import app from '@/config/app.js'
import AppImage from '@/Components/Core/AppImage.vue'
</script>
<template>
  <div class="flex justify-center flex-col mx-auto items-center">
    <AppImage
      :alt="app.getAppName()"
      src="images/logo.png"
      class="w-24 md:w-32 lg:w-40"
    />
    <h1
      id="title"
      class="brand-font text-brand text-lg md:text-5xl lg:text-6xl mt-3 font-bold whitespace-nowrap"
      v-text="app.getAppName()"
    />
  </div>
</template>
