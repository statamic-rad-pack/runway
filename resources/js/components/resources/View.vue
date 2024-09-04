<template>
    <div>
        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="__(title)" />

                <dropdown-list class="rtl:ml-2 ltr:mr-2" v-if="!!this.$scopedSlots.twirldown">
                    <slot name="twirldown" :actionCompleted="actionCompleted" />
                </dropdown-list>

                <div>
                    <a v-if="canCreate" class="btn-primary" :href="createUrl" v-text="createLabel" />
                </div>
            </div>
        </header>

        <runway-listing
            :resource="handle"
            :initial-columns="columns"
            :filters="filters"
            :action-url="actionUrl"
            :primary-column="primaryColumn"
            :has-publish-states="hasPublishStates"
        ></runway-listing>
    </div>
</template>

<script>
import RunwayListing from './Listing.vue'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions'

export default {
    mixins: [HasActions],

    components: {
        RunwayListing,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrl: { type: String, required: true },
        createLabel: { type: String, required: true },
        columns: { type: Array, required: true },
        filters: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        primaryColumn: { type: String, required: true},
        hasPublishStates: { type: Boolean, required: true },
    },

    methods: {
        afterActionSuccessfullyCompleted(response) {
            if (!response.redirect) window.location.reload();
        },
    },
}
</script>
