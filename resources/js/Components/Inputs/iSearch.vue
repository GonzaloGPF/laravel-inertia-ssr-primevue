<script setup lang="ts">
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import { toRefs, watch } from 'vue'
import AppIcon from '@/Components/Core/AppIcon.vue'
import useInput from '@/composables/useInput'
import InputLayout from '@/Layouts/InputLayout.vue'
import { TextInputProps } from '@/types/input'

const props = defineProps<TextInputProps>()
const emits = defineEmits(['update:modelValue'])

const { iValue, iLabel, iName, reset } = useInput<string>(toRefs(props))

watch(iValue, (value) => emits('update:modelValue', value))

defineExpose({
  reset,
})
</script>
<template>
  <InputLayout v-bind="$props">
    <IconField>
      <InputIcon>
        <AppIcon icon="search" class="!text-base" />
      </InputIcon>
      <InputText
        :id="id"
        v-model="iValue"
        :name="iName"
        :placeholder="placeholder ?? iLabel"
        :disabled="disabled"
        :fluid="fluid"
        :invalid="!!error"
        autocomplete="off"
      />
    </IconField>
  </InputLayout>
</template>
