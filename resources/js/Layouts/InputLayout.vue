<script setup lang="ts">
import { computed, toRefs } from 'vue'
import useInput from '@/composables/useInput'
import { InputProps } from '@/types/input'

type Props = InputProps & {
  isCheckbox?: boolean
}

defineOptions({ inheritAttrs: false })
const props = defineProps<Props>()

const { iLabel } = useInput(toRefs(props))

const hintOrError = computed(() => {
  if (props.error) {
    return props.error
  }
  return props.hint
})
const innerContainerClasses = computed(() => ({
  'flex gap-2': true,
  'flex-row-reverse items-center justify-end my-2': props.isCheckbox,
  'flex-col': !props.isCheckbox,
}))
</script>
<template>
  <div class="flex flex-col">
    <div :class="innerContainerClasses">
      <label v-if="!hideLabel" :for="String(id)" v-text="iLabel" />
      <slot />
    </div>
    <div class="h-5">
      <small
        v-if="hintOrError"
        :id="`${id}_help`"
        :class="!!error ? 'text-red-600' : null"
        v-text="hintOrError"
      />
    </div>
  </div>
</template>
