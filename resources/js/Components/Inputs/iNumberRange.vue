<script setup lang="ts">
import INumber from '@/Components/Inputs/iNumber.vue'
import useInput from '@/composables/useInput'
import { Translator } from '@/objects/Translator'
import { onMounted, ref, toRefs, useTemplateRef } from 'vue'
import { NumberInputProps } from '@/types/input'

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])
const props = defineProps<NumberInputProps>()
const { iLabel } = useInput(toRefs(props))

const hasFocus = ref(false)
const iMin = ref<number>()
const iMax = ref<number>()
const minElement = useTemplateRef('minElement')
const maxElement = useTemplateRef('maxElement')

const onModelValue = () => {
  emit('update:modelValue', [iMin.value, iMax.value])
}

const initValues = () => {
  if (!Array.isArray(props.modelValue)) return

  // minElement.value.setValue(props.modelValue[0])
  // maxElement.value.setValue(props.modelValue[1])
  iMin.value = props.modelValue[0]
  iMax.value = props.modelValue[1]
}

const reset = () => {
  if (minElement.value) {
    minElement.value.reset()
  }
  if (maxElement.value) {
    maxElement.value.reset()
  }
  // this.min = this.max = null;
}

onMounted(() => {
  initValues()
})

defineExpose({
  reset,
})
</script>
<template>
  <div class="d-flex flex-row items-center text-center">
    <i-number
      ref="minElement"
      v-model="iMin"
      :name="`min_${name}`"
      :disabled="disabled"
      :label="Translator.tl('min')"
      @focus="$emit('focus')"
      @blur="$emit('blur')"
      @update:model-value="onModelValue"
    />

    <div
      class="d-flex align-center caption mx-2"
      :class="{
        'primary--text': hasFocus,
        'grey--text text--lighten-1': !hasFocus,
      }"
      v-text="iLabel"
    />

    <i-number
      ref="maxElement"
      v-model="iMax"
      :name="`max_${name}`"
      :disabled="disabled"
      :label="Translator.tl('max')"
      @focus="$emit('focus')"
      @blur="$emit('blur')"
      @update:model-value="onModelValue"
    />
  </div>
</template>
