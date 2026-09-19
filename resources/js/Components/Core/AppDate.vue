<script setup lang="ts">
import { Formatter } from '@/objects/Formatter'
import { ref } from 'vue'

type Props = {
  value?: string | number
  humanize?: boolean
  format?: string
}

const formatMap: Record<string, string> = {
  short: 'dd-MM-yyyy',
  long: 'd MMMM yyyy',
  longer: 'dd MMMM yyyy, HH:mm',
  datetime: 'dd-MM-yyyy HH:mm:ss',
  hour: 'H:mm',
}
const props = defineProps<Props>()
const showHumanize = ref(props.humanize)
/*
mounted() {
    if (!props.humanize) return;

    this.interval = setInterval(() => {
        this.formattedDate = this.getFormattedDate()
    }, 60 * 1000) // each minute
},
destroyed() {
    clearInterval(this.interval);
},
*/
</script>
<template>
  <span
    class="whitespace-nowrap hover:cursor-pointer"
    @click="showHumanize = !showHumanize"
    v-text="Formatter.date(value, humanize, formatMap[format || 'short'])"
  />
</template>
