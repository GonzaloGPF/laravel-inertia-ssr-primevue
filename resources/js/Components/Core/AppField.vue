<script setup>
import AppFieldValue from '@/Components/Core/AppFieldValue.vue'
import { computed } from 'vue'
import AppIcon from '@/Components/Core/AppIcon.vue'
import design from '@/config/design.js'

defineEmits(['click', 'close'])
const props = defineProps({
  label: {
    type: String,
    default: null,
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  inline: {
    type: Boolean,
    default: false,
  },
  tooltip: {
    type: String,
    default: null,
  },
  type: {
    type: String,
    default: 'text',
  },
  href: {
    type: String,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  closable: {
    type: Boolean,
    default: false,
  },
  clean: {
    type: Boolean,
    default: false,
  },
  severity: {
    type: String,
    default: 'info',
    validator: design.validSeverity,
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
  size: {
    type: String,
    default: 'medium',
    validator: design.validSize,
  },
  humanize: {
    type: Boolean,
    default: false,
  },
})
const classes = computed(() => ({
  'inline-block': props.inline,
  'text-red': props.severity === 'error',
  'text-yellow': props.severity === 'warning',
  'text-green': props.severity === 'success',
}))
const style = computed(() => ({
  'min-height': props.inline || !props.label ? 'none' : '40px',
}))
// const labelClass = computed(() => ({
//     'text-lg font-medium my-1': props.size === 'large',
//     'text-md font-medium my-1': props.size === 'medium',
//     'text-sm font-medium my-1': props.size === 'small',
//     'inline-block': props.inline,
//     'mr-2': props.inline
// }))
</script>
<template>
  <div
    :style="style"
    :title="label"
    :class="{ 'inline-block align-center': inline }"
  >
    <AppIcon v-if="icon" :icon="icon" :class="{ 'mr-2': label }" size="small" />

    <span v-tooltip="tooltip" v-if="label" v-text="label" />

    <slot>
      <AppFieldValue
        :model-value="modelValue"
        :type="type"
        :href="href"
        :disabled="disabled"
        :class="classes"
        :closable="closable"
        :clean="clean"
        :color="color"
        :format="format"
        :constant="constant"
        class="field-value"
        :humanize="humanize"
        @close="$emit('close', $event)"
        @click="$emit('click', $event)"
      />
    </slot>
  </div>
</template>
