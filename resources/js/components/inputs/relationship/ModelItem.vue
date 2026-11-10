<script setup>
import { RelatedItem } from '@statamic/cms';

defineEmits(['removed']);

defineProps({
    item: Object,
    editable: Boolean,
});
</script>

<template>
    <RelatedItem
        v-bind="$attrs"
        :item
        :editable="editable && item.editable !== false"
        @removed="$emit('removed')"
    />
</template>
