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
                    <div
                        class="flex items-center justify-between p-2 text-sm border-b"
                    >
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
                            class="h-8"
                            v-if="showFilters"
                            ref="search"
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                        />

                        <div class="flex ml-2 space-x-2">
                            <button
                                class="btn btn-sm"
                                v-text="__('Cancel')"
                                v-show="!alwaysShowFilters && showFilters"
                                @click="filtersHide"
                            />
                            <button
                                class="btn btn-sm"
                                v-text="__('Save')"
                                v-show="showFilters && isDirty"
                                @click="$refs.presets.savePreset()"
                            />
                            <button
                                class="btn flex items-center btn-sm w-12"
                                @click="handleShowFilters"
                                v-if="!showFilters"
                                v-tooltip="__('Show Filter Controls (F)')"
                            >
                                <svg-icon name="search" class="w-4 h-4" />
                                <svg-icon name="filter-lines" class="w-4 h-4" />
                            </button>
                            <data-list-column-picker
                                :preferences-key="preferencesKey('columns')"
                            />
                        </div>
                    </div>

                    <div v-show="showFilters">
                        <data-list-filters
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @filter-changed="filterChanged"
                            @search-changed="searchChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                            @restore-preset="$refs.presets.viewPreset($event)"
                            @reset="filtersReset"
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
                            <template
                                :slot="primaryColumn"
                                slot-scope="{ row, value }"
                            >
                                <a :href="row.edit_url" @click.stop>{{
                                    value
                                }}</a>
                            </template>

                            <template
                                slot="actions"
                                slot-scope="{ row, index }"
                            >
                                <dropdown-list
                                    v-if="
                                        canViewRow(row) ||
                                        canEditRow(row) ||
                                        row.actions.length
                                    "
                                >
                                    <dropdown-item
                                        v-if="canViewRow(row)"
                                        :text="__('View')"
                                        :redirect="row.permalink"
                                    />

                                    <dropdown-item
                                        v-if="canEditRow(row)"
                                        :text="__('Edit')"
                                        :redirect="row.edit_url"
                                    />

                                    <div
                                        class="divider"
                                        v-if="
                                            (canViewRow(row) ||
                                                canEditRow(row)) &&
                                            row.actions.length
                                        "
                                    />

                                    <data-list-inline-actions
                                        :item="row.id"
                                        :url="actionUrl"
                                        :actions="row.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
                                <div v-else class="w-10 block"></div>
                            </template>
                        </data-list-table>
                    </div>

                    <confirmation-modal
                        v-if="deletingRow !== false"
                        :title="__('Delete')"
                        :bodyText="
                            __('Are you sure you want to delete this item?')
                        "
                        :buttonText="__('Delete')"
                        :danger="true"
                        @confirm="deleteRow()"
                        @cancel="cancelDeleteRow"
                    ></confirmation-modal>
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

export default {
    mixins: [Listing],

    props: {
        listingConfig: Object,
        initialColumns: Array,
        actionUrl: String,
    },

    data() {
        let primaryColumn = ''

        if (this.initialColumns) {
            this.initialColumns.forEach((column) => {
                if (column.is_primary_column) {
                    primaryColumn = column.handle
                }
            })
        }

        return {
            listingKey: 'id',
            preferencesPrefix: this.listingConfig.preferencesPrefix ?? 'runway',
            requestUrl: this.listingConfig.requestUrl,
            columns: this.initialColumns,
            meta: {},
            primaryColumn: `cell-${primaryColumn}`,
            deletingRow: false,
        }
    },

    methods: {
        canViewRow(row) {
            return row.viewable && row.permalink
        },

        canEditRow(row) {
            return row.editable
        },

        confirmDeleteRow(id, index, deleteUrl) {
            this.visibleColumns = this.columns.filter(
                (column) => column.visible
            )
            this.deletingRow = { id, index, deleteUrl }
        },

        deleteRow(message) {
            const id = this.deletingRow.id
            message = message || __('Deleted')

            this.$axios
                .delete(this.deletingRow.deleteUrl)
                .then(() => {
                    let i = _.indexOf(
                        this.items,
                        _.findWhere(this.rows, { id })
                    )
                    this.items.splice(i, 1)
                    this.deletingRow = false
                    this.$toast.success(message)

                    // location.reload()
                })
                .catch((e) => {
                    this.$toast.error(
                        e.response
                            ? e.response.data.message
                            : __('Something went wrong')
                    )
                })
        },

        cancelDeleteRow() {
            this.deletingRow = false
            setTimeout(() => {
                this.visibleColumns = this.columns.filter(
                    (column) => column.visible
                )
            }, 50)
        },
    },
}
</script>
