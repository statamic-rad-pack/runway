<template>
    <div>
        <label class="mb-2 font-medium publish-field-label" v-text="__('Revisions')"/>
        <div class="flex items-center mb-1" v-if="published">
            <span class="w-6 text-center text-green-600">&check;</span>
            <span class="text-2xs" v-text="__('Entry has a published version')"></span>
        </div>
        <div class="flex items-center mb-1" v-else>
            <span class="w-6 text-center text-orange">!</span>
            <span class="text-2xs" v-text="__('Entry has not been published')"></span>
        </div>
        <div class="flex items-center mb-1" v-if="!isWorkingCopy && published">
            <span class="w-6 text-center text-green-600">&check;</span>
            <span class="text-2xs" v-text="__('This is the published version')"></span>
        </div>
        <div class="flex items-center mb-1" v-if="isDirty">
            <span class="w-6 text-center text-orange">!</span>
            <span class="text-2xs" v-text="__('Unsaved changes')"></span>
        </div>
        <button
            class="flex items-center justify-center w-full px-2 mt-4 btn-flat"
            @click="$emit('show-history')"
        >
            <svg-icon name="light/history" class="w-4 h-4 rtl:ml-2 ltr:mr-2" />
            <span>{{ __('View History') }}</span>
        </button>
    </div>
</template>

<script>
export default {

    props: {
        isDirty: {
            type: Boolean
        },

        isWorkingCopy: {
            type: Boolean
        },

        published: {
            type: Boolean
        },

        revisionsEnabled: {
            type: Boolean
        },
    },

    data() {
        return {
        }
    },

    computed: {
    },

    methods: {
    }
}
</script>
