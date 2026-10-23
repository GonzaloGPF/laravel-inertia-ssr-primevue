<script setup lang="ts">
import { computed } from 'vue'
import AppAvatar from '@/Components/Core/AppAvatar.vue'
import AppField from '@/Components/Core/AppField.vue'
import { Translator } from '@/objects/Translator'
import { User } from '@/types'
import { InputValue } from '@/types/input'

type Props = {
  user: User
}

const EMPTY = '-'

const props = defineProps<Props>()

const fields = computed<{ name: string; value?: InputValue | null }[]>(() => [
  { name: 'name', value: props.user.name },
  { name: 'email', value: props.user.email },
  { name: 'phone', value: props.user.phone },
  { name: 'age', value: props.user.age },
  { name: 'role', value: Translator.tConstName('roles', props.user.role) },
  {
    name: 'language',
    value: Translator.tConstName('languages', props.user.language),
  },
  {
    name: 'currency',
    value: Translator.tConstName('currencies', props.user.currency),
  },
])

const getValue = (value?: InputValue | null) =>
  value === undefined || value === null || value === '' ? EMPTY : value
</script>
<template>
  <div class="flex items-start gap-6">
    <AppAvatar :src="user.avatar_url" :name="user.name" size="xlarge" />
    <div
      class="grid flex-1 grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2 lg:grid-cols-4"
    >
      <AppField
        v-for="field in fields"
        :key="field.name"
        :label="Translator.translate(field.name)"
        :value="getValue(field.value)"
        class="flex flex-col [&>span:first-child]:text-xs [&>span:first-child]:opacity-70"
      />
    </div>
  </div>
</template>
