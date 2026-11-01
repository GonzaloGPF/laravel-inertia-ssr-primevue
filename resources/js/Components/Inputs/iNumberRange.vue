<script setup lang="ts">
import iNumber from '@/Components/Inputs/iNumber.vue'
import InputLayout from '@/Layouts/InputLayout.vue'
import { Translator } from '@/objects/Translator'
import { ref, useTemplateRef, watch } from 'vue'
import { NumberInputProps } from '@/types/input'

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])
const props = defineProps<NumberInputProps>()

const iMin = ref<number>()
const iMax = ref<number>()
const minElement = useTemplateRef('minElement')
const maxElement = useTemplateRef('maxElement')

const onModelValue = () => {
  emit('update:modelValue', [iMin.value, iMax.value])
}

// The range follows the value it is given, as it can change from outside
watch(
  () => props.modelValue,
  (value) => {
    const [min, max] = Array.isArray(value) ? value : []

    iMin.value = min as number | undefined
    iMax.value = max as number | undefined
  },
  { immediate: true, deep: true }
)

const reset = () => {
  minElement.value?.reset()
  maxElement.value?.reset()
}

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <div class="flex items-center gap-2">
      <iNumber
        ref="minElement"
        v-model="iMin"
        :name="`min_${name}`"
        :disabled="disabled"
        :label="Translator.tl('min')"
        :min="min"
        :max="max"
        hide-label
        hide-details
        fluid
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        @update:model-value="onModelValue"
      />

      <span aria-hidden="true">-</span>

      <iNumber
        ref="maxElement"
        v-model="iMax"
        :name="`max_${name}`"
        :disabled="disabled"
        :label="Translator.tl('max')"
        :min="min"
        :max="max"
        hide-label
        hide-details
        fluid
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        @update:model-value="onModelValue"
      />
    </div>
  </InputLayout>
</template>
