<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card overflow-hidden p-0 relative">
                    <div class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b dark:border-dark-900">
                        <data-list-filter-presets
                            ref="presets"
                            v-show="alwaysShowFilters || !showFilters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                            @hide-filters="filtersHide"
                            @show-filters="filtersShow"
                        />

                        <data-list-search
                            class="h-8 mt-2 min-w-[240px] w-full"
                            ref="search"
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                        />

                        <div class="flex space-x-2 mt-2">
                            <button
                                class="btn btn-sm ml-2"
                                v-text="__('Reset')"
                                v-show="isDirty"
                                @click="$refs.presets.refreshPreset()"
                            />
                            <button
                                class="btn btn-sm ml-2"
                                v-text="__('Save')"
                                v-show="isDirty"
                                @click="$refs.presets.savePreset()"
                            />
                            <data-list-column-picker
                                :preferences-key="preferencesKey('columns')"
                            />
                        </div>
                    </div>

                    <div>
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :is-searching="true"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @changed="filterChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                        />
                    </div>

                    <div
                        v-show="items.length === 0"
                        class="p-6 text-center text-gray-500"
                        v-text="__('No results')"
                    />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />

                    <div class="overflow-x-auto overflow-y-hidden">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="true"
                            :loading="loading"
                            :reorderable="false"
                            :sortable="true"
                            :toggle-selection-on-row-click="true"
                            :allow-column-picker="true"
                            :column-preferences-key="preferencesKey('columns')"
                            @sorted="sorted"
                        >
                            <template :slot="`cell-${primaryColumn}`" slot-scope="{ row: model, value }">
                                <a class="title-index-field inline-flex items-center" :href="model.edit_url" @click.stop>
                                    <span class="little-dot rtl:ml-2 ltr:mr-2" v-tooltip="getStatusLabel(model)" :class="getStatusClass(model)" v-if="hasPublishStates && ! columnShowing('status')" />
                                    <span v-text="value" />
                                </a>
                            </template>

                            <template v-if="hasPublishStates" slot="cell-status" slot-scope="{ row: model }">
                                <div class="status-index-field select-none" v-tooltip="getStatusTooltip(model)" :class="`status-${model.status}`" v-text="getStatusLabel(model)" />
                            </template>

                            <template slot="actions" slot-scope="{ row: model, index }">
                                <dropdown-list v-if="(model.viewable && model.permalink) || model.editable || model.actions.length">
                                    <dropdown-item v-if="model.viewable && model.permalink" :text="__('View')" :external-link="model.permalink" />
                                    <dropdown-item v-if="model.editable" :text="__('Edit')" :redirect="model.edit_url" />
                                    <div v-if="((model.viewable && model.permalink) || model.editable) && model.actions.length" class="divider" />
                                    <data-list-inline-actions
                                        :item="model.id"
                                        :url="actionUrl"
                                        :actions="model.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
                                <div v-else class="w-10 block"></div>
                            </template>
                        </data-list-table>
                    </div>
                </div>

                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    :per-page="perPage"
                    @page-selected="selectPage"
                    @per-page-changed="changePerPage"
                />
            </div>
        </data-list>
    </div>
</template>

<script>
import Listing from '../../../../vendor/statamic/cms/resources/js/components/Listing.vue'
import DataListFilters from '../data-list/Filters.vue'

export default {
    mixins: [Listing],

    components: {
        DataListFilters,
    },

    props: {
        resource: String,
        primaryColumn: String,
        hasPublishStates: Boolean,
    },

    data() {
        return {
            listingKey: 'models',
            preferencesPrefix: `runway.${this.resource}`,
            requestUrl: cp_url(`runway/${this.resource}/listing-api`),
            deletingRow: false,
            pushQuery: true,
        }
    },

    methods: {
        getStatusClass(model) {
            if (model.published) {
                return 'bg-green-600';
            } else {
                return 'bg-gray-400';
            }
        },

        getStatusLabel(model) {
            if (model.status === 'published') {
                return __('Published');
            } else if (model.status === 'draft') {
                return __('Draft');
            }
        },

        getStatusTooltip(model) {
            if (model.status === 'published') {
                return null; // Models don't have publish dates.
            } else if (model.status === 'draft') {
                return null; // The label is sufficient.
            }
        },

        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
        },
    },
}
</script>
