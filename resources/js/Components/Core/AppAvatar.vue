<script setup lang="ts">
import Avatar from 'primevue/avatar'
import { computed } from 'vue'
import AppIcon from '@/Components/Core/AppIcon.vue'

type Props = {
  /** Url of the image. Without it, the avatar shows the initials of the name. */
  src?: string | null
  name?: string
  size?: 'normal' | 'large' | 'xlarge'
  /** Shows the name next to the avatar. */
  showName?: boolean
}

const INITIALS = 2

const props = withDefaults(defineProps<Props>(), { size: 'normal' })

const initials = computed(() =>
  (props.name ?? '')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, INITIALS)
    .map((word) => word.charAt(0).toUpperCase())
    .join('')
)
</script>
<template>
  <div :class="{ 'flex items-center gap-3': showName }">
    <Avatar
      :image="src ?? undefined"
      :label="src ? undefined : initials || undefined"
      :size="size"
      :aria-label="name"
      shape="circle"
      class="shrink-0"
    >
      <AppIcon v-if="!src && !initials" icon="person" />
    </Avatar>
    <span v-if="showName" v-text="name" />
  </div>
</template>
