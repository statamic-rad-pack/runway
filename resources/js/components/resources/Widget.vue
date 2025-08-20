<script setup>
import { computed, ref } from 'vue'
import {
    Widget,
    StatusIndicator,
    Listing,
    ListingTableHead as TableHead,
    ListingTableBody as TableBody,
    ListingPagination as Pagination,
    Icon,
} from '@statamic/cms/ui';

const props = defineProps({
    additionalColumns: Array,
    resource: String,
    icon: String,
    title: String,
    initialPerPage: {
        type: Number,
        default: 5,
    },
    initialSortColumn: {
        type: String,
    },
    initialSortDirection: {
        type: String,
    },
    titleColumn: String, // todo
    hasPublishStates: Boolean, // todo
});

const requestUrl = ref(cp_url(`runway/${props.resource}/listing-api`));

const cols = computed(() => [{ field: props.titleColumn, visible: true }, ...props.additionalColumns]);

const widgetProps = computed(() => ({
    title: props.title,
    icon: props.icon,
}));
</script>

<template>
    <Listing
        :url="requestUrl"
        :columns="cols"
        :per-page="initialPerPage"
        :show-pagination-totals="false"
        :show-pagination-page-links="false"
        :show-pagination-per-page-selector="false"
        :sort-column="initialSortColumn"
        :sort-direction="initialSortDirection"
    >
        <template #initializing>
            <Widget v-bind="widgetProps">
                <Icon name="loading" />
            </Widget>
        </template>
        <template #default="{ items, loading }">
            <Widget v-bind="widgetProps">
                <ui-description v-if="!items.length" class="flex-1 flex items-center justify-center">
                    {{ __('There are no models in this resource') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm " :class="{ 'opacity-50': loading }">
                        <TableHead sr-only />
                        <TableBody>
                            <template v-slot:[`cell-${titleColumn}`]="{ row: model, isColumnVisible }">
                                <div class="flex items-center gap-2">
                                    <StatusIndicator v-if="hasPublishStates && !isColumnVisible('status')" :status="model.status" />
                                    <a :href="model.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis" v-text="model[titleColumn]" />
                                </div>
                            </template>
                            <template v-if="hasPublishStates" #cell-status="{ row: model }">
                                <StatusIndicator :status="model.status" :show-dot="false" show-label />
                            </template>
                        </TableBody>
                    </table>
                </div>
                <template #actions>
                    <Pagination />
                    <slot name="actions" />
                </template>
            </Widget>
        </template>
    </Listing>
</template>
