<script setup>
import Formatter from '@/objects/Formatter'
import { ref } from 'vue'

/**
 * @type {{hour: string, short: string, long: string}}
 */
const formatMap = {
  short: 'dd-MM-yyyy',
  long: 'd MMMM yyyy',
  longer: 'dd MMMM yyyy, HH:mm',
  datetime: 'dd-MM-yyyy HH:mm:ss',
  hour: 'H:mm',
}
const props = defineProps({
  modelValue: {
    type: [String, Number],
    // required: true,
    default: null,
  },
  humanize: {
    type: Boolean,
    default: false,
  },
  format: {
    type: String,
    default: 'short',
  },
})

const showHumanize = ref(props.humanize)
/*
mounted() {
    if (!this.humanize) return;

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
    v-if="modelValue"
    class="whitespace-nowrap hover:cursor-pointer"
    @click="showHumanize = !showHumanize"
    v-text="Formatter.date(modelValue, showHumanize, formatMap[format])"
  />
  <span v-else>-</span>
</template>
