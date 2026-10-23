<script setup lang="ts">
import { computed, ref, useTemplateRef, watch } from 'vue'
import AppAvatar from '@/Components/Core/AppAvatar.vue'
import AppButton from '@/Components/Core/AppButton.vue'
import InputLayout from '@/Layouts/InputLayout.vue'
import { Translator } from '@/objects/Translator'
import type { InputProps } from '@/types/input'

type Props = Omit<InputProps, 'modelValue'> & {
  /** Url of the image the model already has. */
  current?: string | null
  /** Used to show the initials when there is no image. */
  fallback?: string
  accept?: string
}

const props = defineProps<Props>()

/** The new image, chosen by the user. */
const file = defineModel<File | null>({ default: null })
/** The user wants the current image to be removed. */
const removed = defineModel<boolean>('removed', { default: false })

const inputElement = useTemplateRef<HTMLInputElement>('inputElement')
const preview = ref<string>()

watch(
  file,
  (value, _previous, onCleanup) => {
    if (!value) {
      preview.value = undefined
      return
    }

    const url = URL.createObjectURL(value)
    preview.value = url
    onCleanup(() => URL.revokeObjectURL(url))
  },
  { immediate: true }
)

const src = computed(
  () => preview.value ?? (removed.value ? undefined : props.current)
)

const onChange = (event: Event) => {
  const input = event.target as HTMLInputElement

  file.value = input.files?.[0] ?? null
  removed.value = false
  // The same file can be chosen again after removing it
  input.value = ''
}

const onRemove = () => {
  if (file.value) {
    file.value = null
  } else {
    removed.value = true
  }
}
</script>
<template>
  <InputLayout v-bind="props" :hide-details="hideDetails">
    <div class="flex flex-col items-start gap-3">
      <AppAvatar :src="src" :name="fallback" size="xlarge" />
      <div class="flex items-center gap-2">
        <AppButton
          :label="Translator.actionTitle('upload')"
          :disabled="disabled"
          icon="upload"
          size="small"
          severity="secondary"
          outlined
          @click="inputElement?.click()"
        />
        <AppButton
          v-if="src"
          :title="Translator.actionTitle('remove')"
          :disabled="disabled"
          icon="delete"
          size="small"
          severity="secondary"
          text
          @click="onRemove"
        />
      </div>
      <input
        :id="id"
        ref="inputElement"
        :name="name"
        :accept="accept"
        :disabled="disabled"
        type="file"
        class="hidden"
        @change="onChange"
      />
    </div>
  </InputLayout>
</template>
