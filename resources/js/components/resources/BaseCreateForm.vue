<template>
    <runway-publish-form
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :resource="resource"
        :resource-has-routes="resourceHasRoutes"
        :initial-title="createLabel"
        :initial-blueprint="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :can-manage-publish-state="canManagePublishState"
        :create-another-url="createAnotherUrl"
        :initial-listing-url="listingUrl"
        @saved="saved"
    ></runway-publish-form>
</template>

<script>
export default {
    props: {
        actions: Object,
        resource: Object,
        resourceHasRoutes: Boolean,
        createLabel: String,
        blueprint: Object,
        values: Object,
        meta: Object,
        canManagePublishState: Boolean,
        createAnotherUrl: String,
        listingUrl: String,
    },

    methods: {
        saved(response) {
            window.location = response.data.data.edit_url + '?created=true';
        },
    },
};
</script>
