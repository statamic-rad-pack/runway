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
            <a class="title-index-field" :href="model.edit_url" @click.stop>
                <StatusIndicator v-if="hasPublishStates && !isColumnVisible('status')" :status="model.status" />
                <span v-text="model[titleColumn]" />
            </a>
        </template>
        <template v-if="hasPublishStates" #cell-status="{ row: model }">
            <StatusIndicator :status="model.status" show-label :show-dot="false" />
        </template>
        <template #prepended-row-actions="{ row: model }">
            <DropdownItem
                v-if="model.viewable && model.permalink"
                :text="__('Visit URL')"
                :href="model.permalink"
                icon="eye"
            />
            <DropdownItem v-if="model.editable" :text="__('Edit')" :href="model.edit_url" icon="edit" />
        </template>
    </Listing>
</template>

<script>
import { StatusIndicator, DropdownItem, Listing } from '@statamic/ui';

export default {
    components: {
        StatusIndicator,
        Listing,
        DropdownItem,
    },

    props: {
        resource: String,
        actionUrl: String,
        columns: Array,
        filters: Array,
        titleColumn: String,
        hasPublishStates: Boolean,
    },

    data() {
        return {
            preferencesPrefix: `runway.${this.resource}`,
            requestUrl: cp_url(`runway/${this.resource}/listing-api`),
            items: null,
            page: null,
            perPage: null,
        };
    },

    methods: {
        requestComplete({ items, parameters, activeFilters }) {
            this.items = items;
            this.page = parameters.page;
            this.perPage = parameters.perPage;
        },
    },
};
</script>
