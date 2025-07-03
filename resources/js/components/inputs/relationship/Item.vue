<template>
    <div class="mb-1.5 last:mb-0">
        <StatamicRelatedItem v-bind="$props" @removed="removed" />

        <ConfirmationModal
            v-if="showDeletionConfirmationModel"
            :title="__('Unlink')"
            :body-text="__('Unlinking this model will result in it being deleted. Are you sure you want to do this?')"
            :button-text="__('Delete')"
            :danger="true"
            @confirm="
                showDeletionConfirmationModel = false;
                $emit('removed');
            "
            @cancel="showDeletionConfirmationModel = false"
        />
    </div>
</template>

<script>
/**
 * Sometimes unlinking a related model will result in it being deleted.
 * Before doing that, we want to show a confirmation modal to the user.
 */

import ConfirmationModal from '@statamic/components/modals/ConfirmationModal.vue';
import StatamicRelatedItem from '@statamic/components/inputs/relationship/Item.vue';

export default {
    components: {
        ConfirmationModal,
        StatamicRelatedItem,
    },

    inject: ['store'],

    props: {
        item: Object,
        config: Object,
        statusIcon: Boolean,
        editable: Boolean,
        sortable: Boolean,
        readOnly: Boolean,
        formComponent: String,
        formComponentProps: Object,
        formStackSize: String,
    },

    data() {
        return {
            showDeletionConfirmationModel: false,
        };
    },

    computed: {
        unlinkBehavior() {
            let fieldtypeMeta = data_get(this.store.meta, this.$parent.$parent.fieldPathKeys.join('.'));

            return fieldtypeMeta.unlinkBehavior;
        },
    },

    methods: {
        removed() {
            if (this.unlinkBehavior === 'delete') {
                this.showDeletionConfirmationModel = true;
                return;
            }

            this.$emit('removed');
        },
    },
};
</script>
