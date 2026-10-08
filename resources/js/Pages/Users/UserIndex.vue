<script lang="ts" setup>
import AppDataTable from '@/Components/Core/AppDataTable.vue'
import AppModal from '@/Components/Core/AppModal.vue'
import UserForm from '@/Components/Forms/UserForm.vue'
import { userColumns } from '@/config/columns'
import useCrud from '@/composables/useCrud'
import { User } from '@/types'

const { table, item, title, modalTitle, showModal, onAction, onSaved } =
  useCrud<User>('user')
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
      v-model="showModal"
      :title="modalTitle"
      class="w-[28rem] max-w-[95vw]"
      modal
      closable
      hide-actions
    >
      <UserForm :user="item" @success="onSaved" @cancel="showModal = false" />
    </AppModal>
  </div>
</template>
