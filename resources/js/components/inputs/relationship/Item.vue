<template>

    <div
        class="item select-none"
        :class="{ 'invalid': item.invalid }"
    >
        <div class="item-move" v-if="sortable">&nbsp;</div>
        <div class="item-inner">
            <div v-if="statusIcon" class="little-dot rtl:ml-2 ltr:mr-2 hidden @sm:block" :class="item.status" />

            <div
                v-if="item.invalid"
                v-tooltip.top="__('An item with this ID could not be found')"
                v-text="__(item.title)" />

            <a v-if="!item.invalid && editable" @click.prevent="edit" v-text="__(item.title)" class="truncate" v-tooltip="item.title" :href="item.edit_url" />

            <div v-if="!item.invalid && !editable" v-text="__(item.title)" />

            <inline-edit-form
                v-if="isEditing"
                :item="item"
                :component="formComponent"
                :component-props="formComponentProps"
                @updated="itemUpdated"
                @closed="isEditing = false"
            />

            <div class="flex items-center flex-1 justify-end">
                <div v-if="item.hint" v-text="item.hint" class="text-4xs text-gray-600 uppercase whitespace-nowrap rtl:ml-2 ltr:mr-2 hidden @sm:block" />

                <div class="flex items-center" v-if="!readOnly">
                    <dropdown-list>
                        <dropdown-item :text="__('Edit')" @click="edit" v-if="editable" />
                        <dropdown-item :text="__('Unlink')" class="warning" @click="remove" />
                    </dropdown-list>
                </div>
            </div>

        </div>

        <confirmation-modal
            v-if="showDeletionConfirmationModel"
            :title="__('Unlink')"
            :danger="true"
            :buttonText="__('Delete')"
            @confirm="showDeletionConfirmationModel = false; $emit('removed')"
            @cancel="showDeletionConfirmationModel = false"
        >
            <div>{{ __('Unlinking this model will result in it being deleted. Are you sure you want to do this?') }}</div>
        </confirmation-modal>

    </div>

</template>

<script>
/**
 * This component is a copy of Statamic's RelatedItem component, but it adds a
 * confirmation modal when deleting models.
 */

import InlineEditForm from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/InlineEditForm.vue';

export default {

    components: {
        InlineEditForm
    },

    inject: {
        storeName: {
            default: null
        }
    },

    props: {
        item: Object,
        config: Object,
        statusIcon: Boolean,
        editable: Boolean,
        sortable: Boolean,
        readOnly: Boolean,
        formComponent: String,
        formComponentProps: Object,
    },

    data() {
        return {
            isEditing: false,
            showDeletionConfirmationModel: false,
        }
    },

    computed: {
        unlinkBehavior() {
            return this.$parent.$parent.meta.unlinkBehavior;
        }
    },

    methods: {

        edit() {
            if (! this.editable) return;
            if (this.item.invalid) return;

            if (this.item.reference && Object.entries(this.$store.state.publish).find(([key, value]) => value.reference === this.item.reference)) {
                this.$toast.error(__("You're already editing this item."));
                return;
            }

            this.isEditing = true;
        },

        itemUpdated(responseData) {
            this.item.title = responseData.title;
            this.item.published = responseData.published;
            this.item.private = responseData.private;
            this.item.status = responseData.status;

            this.$events.$emit(`live-preview.${this.storeName}.refresh`);
        },

        remove() {
            if (this.unlinkBehavior === 'delete') {
                this.showDeletionConfirmationModel = true;
                return;
            }

            this.$emit('removed');
        },

    }

}
</script>
