<script setup lang="ts">
import { computed } from 'vue'
import { Utils } from '@/objects/Utils'
import AppChip from '@/Components/Core/AppChip.vue'
import AppText from '@/Components/Core/AppText.vue'
import AppCheck from '@/Components/Core/AppCheck.vue'
import AppDate from '@/Components/Core/AppDate.vue'
import AppMoney from '@/Components/Core/AppMoney.vue'
import AppPercent from '@/Components/Core/AppPercent.vue'
import AppButton from '@/Components/Core/AppButton.vue'
import AppEmpty from '@/Components/Core/AppEmpty.vue'
import { router } from '@inertiajs/vue3'
import { Formatter } from '@/objects/Formatter'
import { FieldType, InputValue } from '@/types/input'
import { BaseModel } from '@/types/base-model'
import { Translator } from '@/objects/Translator'

type Props = {
  value?: InputValue
  type?: FieldType
  href?: string
  disabled?: boolean
  closable?: boolean
  clean?: boolean
  color?: string
  format?: string
  constant?: string
  humanize?: boolean
}

defineEmits(['click', 'close'])
const props = withDefaults(defineProps<Props>(), {
  type: 'text',
})
const isArray = computed(() => Array.isArray(props.value))
const onLink = (url?: string) => {
  if (!url) {
    return
  }
  if (props.type === 'email') {
    document.location = `mailto:${url}`
    return
  }

  return Utils.openTab(url)
}

const getChipLabel = (chip: BaseModel) => {
  if (props.constant) {
    return Translator.tConstName(props.constant, chip.name)
  }

  return chip.label || chip.name || chip
}

const dateFormat = computed(() => {
  if (props.type === 'time') {
    return 'hour'
  }
  if (props.type === 'datetime') {
    return 'datetime'
  }
  return props.format || 'short'
})
</script>
<template>
  <div v-if="isArray" class="space-x-1">
    <AppChip
      v-for="(chip, index) in value as Array<BaseModel>"
      :key="chip.id || index"
      :closable="closable"
      :value="getChipLabel(chip)"
      @click="$emit('click', chip)"
      @close="$emit('close', chip)"
    />
    <AppEmpty
      v-if="(value as Array<BaseModel>)?.length === 0"
      class="text-sm"
    />
  </div>

  <AppChip
    v-else-if="type === 'chip' && value"
    :closable="closable"
    :value="getChipLabel(value as BaseModel)"
    @click="$emit('click', value)"
    @close="$emit('close', value)"
  />

  <AppText
    v-else-if="!href && (type === 'text' || type === 'html')"
    class="text-sm"
    :value="Formatter.ucFirst(value)"
  />

  <AppCheck
    v-else-if="type === 'boolean'"
    :disabled="disabled"
    :value="!!value"
  />

  <AppDate
    v-else-if="type === 'date' || type === 'time' || type === 'datetime'"
    :disabled="disabled"
    :value="value as any"
    :format="dateFormat"
  />

  <AppMoney
    v-else-if="type === 'money'"
    :disabled="disabled"
    :clean="!!clean"
    :value="value"
  />

  <AppPercent
    v-else-if="type === 'percent'"
    :disabled="disabled"
    :value="value"
  />

  <AppButton
    v-else-if="(type === 'link' || type === 'email') && value"
    :label="String(value)"
    :disabled="disabled"
    color="blue"
    size="small"
    append-icon="mdi-open-in-new"
    variant="text"
    @click="onLink(href)"
  />

  <AppButton
    v-else-if="href"
    :disabled="disabled"
    :label="String(value)"
    :color="color"
    size="small"
    variant="text"
    class="text-left inline-block !normal-case !text-gray-300 !dark:text-gray-300"
    @click="router.visit(href)"
  />

  <span v-else v-text="value" />
</template>
