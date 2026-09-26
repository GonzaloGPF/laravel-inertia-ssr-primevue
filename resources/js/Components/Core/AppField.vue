<script setup lang="ts">
import AppFieldValue from '@/Components/Core/AppFieldValue.vue'
import { computed } from 'vue'
import AppIcon from '@/Components/Core/AppIcon.vue'
import { FieldType, InputValue } from '@/types/input'
import { Severity, Size } from '@/types/design'

type Props = {
  label?: string
  value?: InputValue
  icon?: string
  inline?: boolean
  tooltip?: string
  type?: FieldType
  href?: string
  disabled?: boolean
  closable?: boolean
  clean?: boolean
  severity?: Severity
  color?: string
  format?: string
  constant?: string
  size?: Size
  humanize?: boolean
}

defineEmits(['click', 'close'])
const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  severity: 'info',
  size: 'medium',
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
        :value="value"
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
