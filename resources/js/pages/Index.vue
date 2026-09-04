<script setup>
import { Head, Link } from '@statamic/cms/inertia'
import { ItemActions } from '@statamic/cms';
import {
    Header,
    Dropdown,
    DropdownMenu,
    DropdownLabel,
    DropdownItem,
    DropdownSeparator,
    Button,
    Listing,
    StatusIndicator,
} from '@statamic/cms/ui'
import { getCurrentInstance, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    icon: { type: String, required: true },
    title: { type: String, required: true },
    resource: { type: String, required: true },
    canCreate: { type: Boolean, required: true },
    createUrl: { type: String, required: true },
    createLabel: { type: String, required: true },
    columns: { type: Array, required: true },
    sortColumn: { type: String, required: true },
    sortDirection: { type: String, required: true },
    filters: { type: Array, required: true },
    actions: { type: Array, required: true },
    actionUrl: { type: String, required: true },
    modelsActionUrl: { type: String, required: true },
    blueprintUrl: { type: String, required: true },
    canEditBlueprint: { type: Boolean, required: true },
    hasPublishStates: { type: Boolean, required: true },
    titleColumn: { type: String, required: true },
    reorderable: { type: Boolean, required: true },
    reorderUrl: { type: String, required: true },
});

const { $axios, $toast, $keys } = getCurrentInstance().appContext.config.globalProperties;

const preferencesPrefix = ref(`runway.${props.resource}`);
const requestUrl = ref(cp_url(`runway/${props.resource}/listing-api`));
const items = ref(null);
const page = ref(null);
const perPage = ref(null);
const reordering = ref(false);

let saveKeyBinding = null;

function requestComplete({ items: newItems, parameters }) {
    items.value = newItems;
    page.value = parameters.page;
    perPage.value = parameters.perPage;
}

function reordered(newItems) {
    items.value = newItems;
}

function saveOrder() {
    const payload = {
        ids: items.value.map((item) => item.id),
        page: page.value,
        perPage: perPage.value,
    };

    $axios
        .post(props.reorderUrl, payload)
        .then(() => {
            reordering.value = false;
            $toast.success(__('Order saved'));
        })
        .catch((e) => {
            $toast.error(e.response?.data?.message || __('Something went wrong'));
        });
}

onMounted(() => {
    saveKeyBinding = $keys.bindGlobal(['mod+s'], (e) => {
        if (reordering.value) {
            e.preventDefault();
            saveOrder();
        }
    });
});

onBeforeUnmount(() => saveKeyBinding.destroy());
</script>

<template>
    <Head :title="[__(title)]" />

    <div>
        <Header :title="__(title)" :icon>
            <ItemActions
                :url="actionUrl"
                :actions="actions"
                :item="resource"
                v-slot="{ actions }"
            >
                <Dropdown placement="left-start" class="me-2">
                    <DropdownMenu>
                        <DropdownLabel :text="__('Actions')" />
                        <DropdownItem
                            v-if="canEditBlueprint"
                            :text="__('Edit Blueprint')"
                            icon="blueprint-edit"
                            :href="blueprintUrl"
                        />
                        <DropdownSeparator v-if="canEditBlueprint && actions.length" />
                        <DropdownItem
                            v-for="action in actions"
                            :key="action.handle"
                            :text="__(action.title)"
                            :icon="action.icon"
                            :variant="action.dangerous ? 'destructive' : 'default'"
                            @click="action.run"
                        />
                    </DropdownMenu>
                </Dropdown>
            </ItemActions>

            <template v-if="reorderable">
                <Button v-if="!reordering" :text="__('Reorder')" @click="reordering = true" />

                <template v-if="reordering">
                    <Button :text="__('Cancel')" @click="reordering = false" />
                    <Button variant="primary" :text="__('Save Order')" @click="saveOrder" />
                </template>
            </template>

            <Button v-if="canCreate && !reordering" variant="primary" :text="createLabel" :href="createUrl" />
        </Header>

        <Listing
            ref="listing"
            :url="requestUrl"
            :columns
            :sortColumn="props.sortColumn"
            :sortDirection="props.sortDirection"
            :action-url="modelsActionUrl"
            :action-context="{ resource }"
            :preferences-prefix
            :filters
            :reorderable="reordering"
            push-query
            @request-completed="requestComplete"
            @reordered="reordered"
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
                <DropdownItem v-if="model.viewable && model.permalink" :text="__('Visit URL')" :href="model.permalink" target="_blank" icon="eye" />
                <DropdownItem v-if="model.editable" :text="__('Edit')" :href="model.edit_url" icon="edit" />
            </template>
        </Listing>
    </div>
</template>
