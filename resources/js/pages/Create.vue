<script setup>
import PublishForm from '../components/resources/PublishForm.vue';
// import { Head, router } from '@inertiajs/vue3';

defineProps([
    'actions',
    'resource',
    'resourceHasRoutes',
    'title',
    'blueprint',
    'values',
    'meta',
    'canEditBlueprint',
    'canManagePublishState',
    'createAnotherUrl',
    'listingUrl',
]);

function saved(response) {
    // router.get(response.data.data.edit_url + '?created=true');

    window.location = response.data.data.edit_url + '?created=true'; // todo: use inertia router exported by statamic core
}
</script>

<template>
<!--    <Head :title="title" />-->

    <PublishForm
        :is-creating="true"
        publish-container="base"
        :initial-actions="actions"
        method="post"
        :resource
        :resource-has-routes
        :initial-title="title"
        :initial-blueprint="blueprint"
        :initial-values="values"
        :initial-meta="meta"
        :can-edit-blueprint
        :can-manage-publish-state
        :create-another-url
        :initial-listing-url="listingUrl"
        @saved="saved"
    />
</template>
