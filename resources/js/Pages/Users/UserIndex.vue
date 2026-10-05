<script lang="ts" setup>
import AppDataTable from '@/Components/Core/AppDataTable.vue'
import { userColumns } from '@/config/columns'
import { Translator } from '@/objects/Translator'
</script>
<template>
  <AppDataTable
    :src="route('users.index')"
    :columns="userColumns"
    :header-actions="['create']"
    :title="Translator.modelTitle('user', true)"
  />
</template>
