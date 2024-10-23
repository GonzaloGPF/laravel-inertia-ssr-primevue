<script setup>
defineEmits(['close'])
defineProps({
    modelValue: {
        type: [String, Number, Object],
        required: true,
        default: null
    },
    closable: {
        type: Boolean,
        default: null
    },
    color: {
        type: String,
        default: null
    },
    label: {
        type: Boolean,
        default: null
    },
    size: {
        type: String,
        default: undefined,
        validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
    },
    icon: {
        type: String,
        default: null,
    },
    image: {
        type: String,
        default: null,
    },
})
</script>
<template>
    <Chip
        :label="modelValue.label || modelValue.name || modelValue"
        :icon="icon"
        :removable="closable"
        :image="image"
        @remove="$emit('close')"
    />
</template>
<style>
.v-chip.white {
    color: black;
}
</style>
