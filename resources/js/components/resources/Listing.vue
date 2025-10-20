<script setup>
import { Link } from '@statamic/cms/inertia';
import { StatusIndicator, DropdownItem, Listing } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    resource: String,
    actionUrl: String,
    columns: Array,
    filters: Array,
    titleColumn: String,
    hasPublishStates: Boolean,
});

const preferencesPrefix = ref(`runway.${props.resource}`);
const requestUrl = ref(cp_url(`runway/${props.resource}/listing-api`));
const items = ref(null);
const page = ref(null);
const perPage = ref(null);

function requestComplete({ items: newItems, parameters }) {
    items.value = newItems;
    page.value = parameters.page;
    perPage.value = parameters.perPage;
}
</script>

<template>
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :action-context="{ resource }"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        push-query
        @request-completed="requestComplete"
    >
        <template v-slot:[`cell-${titleColumn}`]="{ row: model, isColumnVisible }">
            <Link class="title-index-field" :href="model.edit_url" @click.stop>
                <StatusIndicator v-if="hasPublishStates && !isColumnVisible('status')" :status="model.status" />
                <span v-text="model[titleColumn]" />
            </Link>
        </template>
        <template v-if="hasPublishStates" #cell-status="{ row: model }">
            <StatusIndicator :status="model.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: model }">
            <DropdownItem
                v-if="model.viewable && model.permalink"
                :text="__('Visit URL')"
                :href="model.permalink"
                target="_blank"
                icon="eye"
            />
            <DropdownItem v-if="model.editable" :text="__('Edit')" :href="model.edit_url" icon="edit" />
        </template>
    </Listing>
</template>
