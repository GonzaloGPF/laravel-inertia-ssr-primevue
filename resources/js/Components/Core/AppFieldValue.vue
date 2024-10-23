<script setup>
import { computed } from 'vue'
import Utils from '@/objects/Utils'
import AppChip from '@/Components/Core/AppChip.vue'
import AppText from '@/Components/Core/AppText.vue'
import AppCheck from '@/Components/Core/AppCheck.vue'
import AppDate from '@/Components/Core/AppDate.vue'
import AppMoney from '@/Components/Core/AppMoney.vue'
import AppPercent from '@/Components/Core/AppPercent.vue'
import AppButton from '@/Components/Core/AppButton.vue'
import AppEmpty from '@/Components/Core/AppEmpty.vue'
import Translator from '@/objects/Translator'
import { router } from '@inertiajs/vue3'
import Formatter from '@/objects/Formatter.js'

defineEmits(['click', 'close'])
const props = defineProps({
  modelValue: {
    type: [String, Number, Array, Boolean, Object],
    default: null,
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) =>
      [
        null,
        'chip',
        'text',
        'html',
        'boolean',
        'date',
        'time',
        'datetime',
        'money',
        'percent',
        'link',
        'email',
      ].includes(value),
  },
  href: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: null,
  },
  closable: {
    type: Boolean,
    default: false,
  },
  clean: {
    type: Boolean,
    default: false,
  },
  color: {
    type: String,
    default: null,
  },
  format: {
    type: String,
    default: null,
  },
  constant: {
    type: String,
    default: null,
  },
  humanize: {
    type: Boolean,
    default: false,
  },
})
const isArray = computed(() => Array.isArray(props.modelValue))
const onLink = (url) => {
  if (props.type === 'email') {
    document.location = `mailto:${url}`
    return
  }

  return Utils.openTab(url)
}

const getChipLabel = (chip) => {
  if (props.constant) {
    return Translator.tConstName(props.constant, chip)
  }

  return chip.label || chip.name || chip
}

const dateFormat = () => {
  if (props.type === 'time') {
    return 'hour'
  }
  if (props.type === 'datetime') {
    return 'datetime'
  }
  return props.format || 'short'
}
</script>
<template>
  <div v-if="isArray" class="space-x-1">
    <AppChip
      v-for="(chip, index) in modelValue"
      :key="chip.id || index"
      :closable="closable"
      :model-value="getChipLabel(chip)"
      @click="$emit('click', chip)"
      @close="$emit('close', chip)"
    />
    <AppEmpty v-if="modelValue.length === 0" class="text-sm" />
  </div>

  <AppChip
    v-else-if="type === 'chip' && modelValue"
    :closable="closable"
    :model-value="getChipLabel(modelValue)"
    @click="$emit('click', modelValue)"
    @close="$emit('close', modelValue)"
  />

  <AppText
    v-else-if="!href && (type === 'text' || type === 'html')"
    class="text-sm"
    :model-value="Formatter.ucFirst(modelValue)"
  />

  <AppCheck
    v-else-if="type === 'boolean'"
    :disabled="disabled"
    :model-value="modelValue"
  />

  <AppDate
    v-else-if="type === 'date' || type === 'time' || type === 'datetime'"
    :disabled="disabled"
    :model-value="modelValue"
    :format="dateFormat"
  />

  <AppMoney
    v-else-if="type === 'money'"
    :disabled="disabled"
    :clean="clean"
    :model-value="modelValue"
  />

  <AppPercent
    v-else-if="type === 'percent'"
    :disabled="disabled"
    :model-value="modelValue"
  />

  <AppButton
    v-else-if="(type === 'link' || type === 'email') && modelValue"
    :label="modelValue"
    :disabled="disabled"
    color="blue"
    size="small"
    append-icon="mdi-open-in-new"
    variant="text"
    @click="onLink(href ?? modelValue)"
  />

  <AppButton
    v-else-if="href"
    :disabled="disabled"
    :label="modelValue"
    :color="color"
    size="small"
    variant="text"
    class="text-left inline-block !normal-case !text-gray-300 !dark:text-gray-300"
    @click="router.visit(href)"
  />

  <span v-else v-text="modelValue" />
</template>
