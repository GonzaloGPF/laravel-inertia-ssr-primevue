<script setup lang="ts">
import Checkbox from 'primevue/checkbox'
import useInput from '@/composables/useInput'
import { toRefs } from 'vue'
import InputLayout from '@/Layouts/InputLayout.vue'
import { CheckInputProps } from '@/types/input.js'

const props = defineProps<CheckInputProps>()
const { iLabel, iName, iValue, reset } = useInput(toRefs(props))

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props" is-checkbox>
    <Checkbox
      v-model="iValue"
      :name="iName"
      :title="iLabel"
      :disabled="disabled"
      :invalid="!!error"
      :required="required"
      :binary="binary"
      :class="{ required }"
      :indeterminate="indeterminate"
    />
  </InputLayout>
</template>
