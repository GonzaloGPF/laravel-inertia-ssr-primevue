<script lang="ts" setup>
import AppDataTable from '@/Components/Core/AppDataTable.vue'
import AppModal from '@/Components/Core/AppModal.vue'
import UserForm from '@/Components/Forms/UserForm.vue'
import { userColumns } from '@/config/columns'
import { userFilters } from '@/config/filters/userFilters'
import useAuth from '@/composables/useAuth'
import useCrud from '@/composables/useCrud'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'

const { user: authUser } = useAuth()

const { table, item, title, modalTitle, showModal, onAction, onSaved } =
  useCrud<User>('user', {
    deleteGuard: (user) =>
      user.id === authUser.value?.id
        ? Translator.t('exceptions.cannot_delete_yourself')
        : undefined,
  })
</script>
<template>
  <div>
    <AppDataTable
      ref="table"
      :src="route('users.index')"
      :columns="userColumns"
      :filters-data="userFilters"
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
