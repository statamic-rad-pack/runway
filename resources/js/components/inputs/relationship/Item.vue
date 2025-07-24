<script setup>
/**
 * Sometimes unlinking a related model will result in it being deleted.
 * Before doing that, we want to show a confirmation modal to the user.
 */

import ConfirmationModal from '@statamic/components/modals/ConfirmationModal.vue';
import StatamicRelatedItem from '@statamic/components/inputs/relationship/Item.vue';
import { inject, ref, computed, getCurrentInstance } from 'vue'

const store = inject('store');
const instance = getCurrentInstance();

const emit = defineEmits(['removed']);

const props = defineProps({
    item: Object,
    config: Object,
    statusIcon: Boolean,
    editable: Boolean,
    sortable: Boolean,
    readOnly: Boolean,
    formComponent: String,
    formComponentProps: Object,
    formStackSize: String,
});

const showDeletionConfirmationModel = ref(false);

const unlinkBehavior = computed(() => {
    const storeKey = instance.parent.parent.props.fieldPathKeys?.join('.') || instance.parent.parent.props.handle;

    return data_get(store.meta, storeKey)?.unlinkBehavior;
});

function removed() {
    if (unlinkBehavior.value === 'delete') {
        showDeletionConfirmationModel.value = true;
        return;
    }

    emit('removed');
}

function confirmDeletion() {
    showDeletionConfirmationModel.value = false;
    emit('removed');
}
</script>

<template>
    <div class="mb-1.5 last:mb-0">
        <StatamicRelatedItem v-bind="$props" @removed="removed" />

        <ConfirmationModal
            v-if="showDeletionConfirmationModel"
            :title="__('Unlink')"
            :body-text="__('Unlinking this model will result in it being deleted. Are you sure you want to do this?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="confirmDeletion"
            @cancel="showDeletionConfirmationModel = false"
        />
    </div>
</template>
