<script setup>
import AppAlert from '@/Components/Core/AppAlert.vue'
import { useFlashMessages } from '@/stores/flashMessages'
import { storeToRefs } from 'pinia'
import { usePage } from '@inertiajs/vue3'
import { computed, ref, toRefs, watch } from 'vue'

const { props } = toRefs(usePage())
const { flashMessages } = storeToRefs(useFlashMessages())

const flashMessageData = computed(() => props.value.flash_message_data)
const fromBack = ref(false)

const pushFlashMessage = () => {
  if (fromBack.value) {
    fromBack.value = false
  }
  useFlashMessages().pushFlashMessage(flashMessageData.value)
}

watch(
  flashMessageData,
  () => {
    setTimeout(pushFlashMessage, 50)
  },
  { immediate: true }
)

addEventListener('popstate', () => {
  fromBack.value = true
})
</script>
<template>
  <div v-if="flashMessages && flashMessages.length" class="messages-container">
    <TransitionGroup
      name="message"
      tag="ul"
      appear
      class="flex justify-center flex-column"
      style="
        list-style: none;
        position: relative;
        min-width: 300px;
        min-height: 100px;
      "
    >
      <li
        v-for="flashMessage in flashMessages"
        :key="flashMessage.message"
        class="my-2"
      >
        <AppAlert
          :title="flashMessage.title"
          :message="flashMessage.message"
          :severity="flashMessage.type"
          :closable="flashMessage.closable"
          :border="flashMessage.closable"
          :icon="flashMessage.icon"
          style="width: 300px"
        />
      </li>
    </TransitionGroup>
  </div>
</template>
<style>
.messages-container {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  width: 500px;
  margin: auto;
  max-width: calc(100% - 30px);
  z-index: 9999;
  overflow: hidden;
  transition: all 0.5s ease;
}

.message-move,
.message-enter-active,
.message-leave-active {
  transition: all 0.5s ease;
}

.message-enter-from,
.message-leave-to {
  opacity: 0;
  transform: translateY(-50px);
}

.message-leave-active {
  position: absolute;
}
</style>
