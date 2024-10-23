<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: null
    }
})
</script>
<template>
    <div v-html="modelValue" />
</template>
