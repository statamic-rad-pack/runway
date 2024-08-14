<template>
    <div>
        <header class="mb-6">
            <div class="flex items-center">
                <h1 class="flex-1" v-text="__(title)" />

                <div>
                    <a v-if="canCreate" class="btn-primary" :href="createUrl" v-text="createLabel" />
                </div>
            </div>
        </header>

        <runway-listing
            :resource="handle"
            :initial-sort-column="sortColumn"
            :initial-sort-direction="sortDirection"
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

export default {
    components: {
        RunwayListing,
    },

    props: {
        title: { type: String, required: true },
        handle: { type: String, required: true },
        canCreate: { type: Boolean, required: true },
        createUrl: { type: String, required: true },
        createLabel: { type: String, required: true },
        sortColumn: { type: String, required: true },
        sortDirection: { type: String, required: true },
        columns: { type: Array, required: true },
        filters: { type: Array, required: true },
        actionUrl: { type: String, required: true },
        primaryColumn: { type: String, required: true},
        hasPublishStates: { type: Boolean, required: true },
    },
}
</script>
