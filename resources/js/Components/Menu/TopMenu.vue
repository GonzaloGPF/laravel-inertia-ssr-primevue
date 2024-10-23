<script lang="ts" setup>
import Menubar from 'primevue/menubar'
import { computed } from 'vue'
import AppNavLink from '@/Components/Core/AppNavLink.vue'
import AppIcon from '@/Components/Core/AppIcon.vue'
import AppImage from '@/Components/Core/AppImage.vue'
import TopMenuAuth from '@/Components/Menu/TopMenuAuth.vue'

const items = computed(() => [
  {
    label: 'Home',
    icon: 'home',
    href: '/',
  },
  {
    label: 'About',
    icon: 'info',
    href: route('about.show'),
  },
])
</script>
<template>
  <Menubar :model="items">
    <template #start>
      <AppImage src="/images/logo.png?test=new" width="32" />
    </template>
    <template #item="{ item, props }">
      <AppNavLink v-if="item.href" v-bind="props.action" :href="item.href">
        <div class="flex items-center justify-center">
          <AppIcon :icon="item.icon" />
          <span class="ml-2">{{ item.label }}</span>
          <!--<span v-if="hasSubmenu" class="pi pi-fw pi-angle-down ml-2" />-->
        </div>
      </AppNavLink>
    </template>
    <template #end>
      <TopMenuAuth />
    </template>
  </Menubar>
</template>
