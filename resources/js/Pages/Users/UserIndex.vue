<script lang="ts" setup>
import AppDataTable from '@/Components/Core/AppDataTable.vue'
import AppModal from '@/Components/Core/AppModal.vue'
import UserForm from '@/Components/Forms/UserForm.vue'
import { userColumns } from '@/config/columns'
import useCrud from '@/composables/useCrud'

const { table, title, createTitle, showCreateModal, onAction, onCreated } =
  useCrud('user')
</script>
<template>
  <div>
    <AppDataTable
      ref="table"
      :src="route('users.index')"
      :columns="userColumns"
      :header-actions="['create']"
      :title="title"
      @action="onAction"
    />
    <AppModal
      v-model="showCreateModal"
      :title="createTitle"
      class="w-[28rem] md:w-[50%] max-w-[95vw]"
      modal
      closable
      hide-actions
    >
      <UserForm @success="onCreated" @cancel="showCreateModal = false" />
    </AppModal>
  </div>
</template>
