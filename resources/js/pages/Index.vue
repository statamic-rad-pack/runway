<script setup>
import { ItemActions } from '@statamic/cms';
import { Header, Dropdown, DropdownMenu, DropdownLabel, DropdownItem, DropdownSeparator, Button } from '@statamic/cms/ui';
import RunwayListing from '../components/resources/Listing.vue';
// import Head from '@/pages/layout/Head.vue';

defineProps({
    icon: { type: String, required: true },
    title: { type: String, required: true },
    handle: { type: String, required: true },
    canCreate: { type: Boolean, required: true },
    createUrl: { type: String, required: true },
    createLabel: { type: String, required: true },
    columns: { type: Array, required: true },
    filters: { type: Array, required: true },
    actions: { type: Array, required: true },
    actionUrl: { type: String, required: true },
    modelsActionUrl: { type: String, required: true },
    blueprintUrl: { type: String, required: true },
    canEditBlueprint: { type: Boolean, required: true },
    hasPublishStates: { type: Boolean, required: true },
    titleColumn: { type: String, required: true },
});
</script>

<template>
    <div>
<!--        <Head :title="[__(title)]" />-->
        <Header :title="__(title)" :icon>
            <ItemActions
                :url="actionUrl"
                :actions="actions"
                :item="handle"
                @started="actionStarted"
                @completed="actionCompleted"
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

            <Button v-if="canCreate" variant="primary" :text="createLabel" :href="createUrl" />
        </Header>

        <RunwayListing
            :resource="handle"
            :columns
            :filters
            :action-url="modelsActionUrl"
            :title-column
            :has-publish-states
        />
    </div>
</template>
