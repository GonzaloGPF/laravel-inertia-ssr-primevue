<script setup>
import INumber from '@/Components/Inputs/iNumber.vue'
import useInput from '@/composables/useInput'
import Translator from '@/objects/Translator'
import { numberProps } from '@/objects/Props'
import { onMounted, ref, toRefs } from 'vue'

const emit = defineEmits(['update:modelValue', 'focus', 'blur'])
const props = defineProps({ ...numberProps })
const { iLabel } = useInput(toRefs(props))

const hasFocus = ref(false)
const iMin = ref(null)
const iMax = ref(null)
const minElement = ref(null)
const maxElement = ref(null)

const onModelValue = () => {
  let minValue = parseInt(iMin.value)
  let maxValue = parseInt(iMax.value)

  if (isNaN(minValue)) {
    minValue = null
  }

  if (isNaN(maxValue)) {
    maxValue = null
  }

  emit('update:modelValue', [minValue, maxValue])
}

const initValues = () => {
  if (!Array.isArray(props.modelValue)) return

  minElement.value.setValue(props.modelValue[0])
  maxElement.value.setValue(props.modelValue[1])
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
