<template>
    <div
        class="relationship-input"
        :class="{ 'relationship-input-empty': items.length == 0 }"
    >
        <loading-graphic v-if="initializing" :inline="true" />

        <template v-if="!initializing">
            <data-list
                class="mb-2"
                :rows="items"
                :columns="columns"
                :sort="false"
            >
                <div>
                    <data-list-table
                        :loading="loading"
                        :reorderable="false"
                        :sortable="false"
                        class="card p-1"
                        @sorted="sorted"
                    >
                        <template v-if="items.length === 0" slot="tbody-start">
                            <div
                                class="p-2 text-grey-70"
                                v-text="__('No results')"
                            ></div>
                        </template>

                        <template
                            :slot="primaryColumn"
                            slot-scope="{ row, value }"
                        >
                            <a @click="edit(row)">{{ value }}</a>
                        </template>

                        <template slot="actions" slot-scope="{ row, index }">
                            <dropdown-list
                                v-if="canViewRow(row) || canEditRow(row)"
                            >
                                <dropdown-item
                                    v-if="canViewRow(row)"
                                    :text="__('View')"
                                    :redirect="row.permalink"
                                />

                                <dropdown-item
                                    v-if="canEditRow(row)"
                                    :text="__('Edit')"
                                    @click="edit(row)"
                                />

                                <dropdown-item
                                    class="warning"
                                    :text="__('Unlink')"
                                    @click="remove(index)"
                                />
                            </dropdown-list>
                            <div v-else class="w-10 block"></div>
                        </template>
                    </data-list-table>
                </div>
            </data-list>

            <inline-edit-form
                v-if="isEditing"
                :item="currentItem"
                :component="formComponent"
                :component-props="formComponentProps"
                @updated="itemUpdated"
                @closed="
                    isEditing = false
                    currentItem = null
                "
            />

            <div
                class="py-1 text-xs text-grey"
                v-if="maxItemsReached && maxItems != 1"
            >
                <span>{{ __('Maximum items selected:') }}</span>
                <span>{{ maxItems }}/{{ maxItems }}</span>
            </div>
            <div
                v-if="canSelectOrCreate"
                class="relationship-input-buttons relative"
                :class="{ 'mt-2': items.length > 0 }"
            >
                <div class="flex flex-wrap items-center text-sm -mb-1">
                    <div class="relative mb-1">
                        <create-button
                            v-if="canCreate && creatables.length"
                            :creatables="creatables"
                            :site="site"
                            :component="formComponent"
                            :component-props="formComponentProps"
                            @created="itemCreated"
                        />
                    </div>
                    <button
                        ref="existing"
                        class="text-blue hover:text-grey-80 flex mb-1 outline-none"
                        @click.prevent="isSelecting = true"
                    >
                        <svg-icon
                            name="hyperlink"
                            class="mr-sm h-4 w-4 flex items-center"
                        ></svg-icon>
                        {{ __('Link Existing Item') }}
                    </button>
                </div>
            </div>

            <stack
                name="item-selector"
                v-if="isSelecting"
                @closed="isSelecting = false"
            >
                <item-selector
                    slot-scope="{ close }"
                    :filters-url="filtersUrl"
                    :selections-url="selectionsUrl"
                    :site="site"
                    :initial-columns="columns"
                    initial-sort-column="title"
                    initial-sort-direction="asc"
                    :initial-selections="value"
                    :max-selections="maxItems"
                    :search="search"
                    :exclusions="exclusions"
                    @selected="selectionsUpdated"
                    @closed="close"
                />
            </stack>

            <input
                v-if="name"
                type="hidden"
                :name="name"
                :value="JSON.stringify(value)"
            />
        </template>
    </div>
</template>

<script>
import RelatedItem from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/Item.vue'
import ItemSelector from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/Selector.vue'
import CreateButton from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/CreateButton.vue'
import { Sortable, Plugins } from '@shopify/draggable'
import RelationshipSelectField from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/SelectField.vue'
import InlineEditForm from '../../../../../vendor/statamic/cms/resources/js/components/inputs/relationship/InlineEditForm.vue'

export default {
    props: {
        name: String,
        value: { required: true },
        config: Object,
        data: Array,
        maxItems: Number,
        itemComponent: {
            type: String,
            default: 'RelatedItem',
        },
        itemDataUrl: String,
        filtersUrl: String,
        selectionsUrl: String,
        statusIcons: Boolean,
        site: String,
        search: Boolean,
        canEdit: Boolean,
        canCreate: Boolean,
        canReorder: Boolean,
        readOnly: Boolean,
        exclusions: Array,
        creatables: Array,
        formComponent: String,
        formComponentProps: Object,
        mode: {
            type: String,
            default: 'default',
        },
        taggable: Boolean,
        columns: {
            type: Array,
            default: () => [],
        },
    },

    components: {
        ItemSelector,
        RelatedItem,
        CreateButton,
        RelationshipSelectField,
        InlineEditForm,
    },

    data() {
        let primaryColumn = ''

        if (this.columns.length) {
            primaryColumn = this.columns[0].field
        }

        return {
            isSelecting: false,
            isCreating: false,
            isEditing: false,
            itemData: [],
            initializing: true,
            loading: true,
            inline: false,
            primaryColumn: `cell-${primaryColumn}`,
            currentItem: null,
        }
    },

    computed: {
        items() {
            return this.value.map(selection => {
                const data = _.find(this.data, item => item.id == selection)

                if (!data) return { id: selection, title: selection }

                return data
            })
        },

        maxItemsReached() {
            return this.value.length >= this.maxItems
        },

        canSelectOrCreate() {
            return !this.readOnly && !this.maxItemsReached
        },
    },

    mounted() {
        this.initializeData().then(() => {
            this.initializing = false
            this.$nextTick(() => this.makeSortable())
        })
    },

    beforeDestroy() {
        this.setLoadingProgress(false)
    },

    watch: {
        loading: {
            immediate: true,
            handler(loading) {
                this.$emit('loading', loading)
                this.setLoadingProgress(loading)
            },
        },

        isSelecting(selecting) {
            this.$emit(selecting ? 'focus' : 'blur')
        },

        itemData(data, olddata) {
            if (this.initializing) return
            this.$emit('item-data-updated', data)
        },
    },

    methods: {
        canViewRow(row) {
            return row.viewable && row.permalink
        },

        canEditRow(row) {
            return row.editable
        },

        edit(item) {
            if (!this.canEditRow(item)) {
                return
            }

            this.isEditing = true
            this.currentItem = item
        },

        itemUpdated(responseData) {
            this.isEditing = false
            this.currentItem = null

            this.initializing = true

            let data = this.data.map(item => {
                if (item.id == responseData.id) {
                    console.log('found the one')

                    return {
                        ...item,
                        ...responseData,
                    }
                }

                return item
            })

            this.$emit('item-data-updated', data)

            this.initializing = false
        },

        update(selections) {
            if (JSON.stringify(selections) == JSON.stringify(this.value)) return
            this.$emit('input', selections)
        },

        remove(index) {
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1),
            ])
        },

        selectionsUpdated(selections) {
            this.getDataForSelections(selections).then(() => {
                this.update(selections)
            })
        },

        initializeData() {
            if (!this.data) {
                return this.getDataForSelections(this.selections)
            }

            this.loading = false
            return Promise.resolve()
        },

        getDataForSelections(selections) {
            this.loading = true

            return this.$axios
                .post(this.itemDataUrl, { site: this.site, selections })
                .then(response => {
                    this.$emit('item-data-updated', response.data.data)
                })
                .finally(() => {
                    this.loading = false
                })
        },

        makeSortable() {
            new Sortable(this.$refs.items, {
                draggable: '.item',
                handle: '.item-move',
                mirror: { constrainDimensions: true, xAxis: false },
                swapAnimation: { vertical: true },
                plugins: [Plugins.SwapAnimation],
                delay: 200,
            })
                .on('drag:start', e => {
                    this.value.length === 1 ? e.cancel() : this.$emit('focus')
                })
                .on('drag:stop', e => {
                    this.$emit('blur')
                })
                .on('sortable:stop', e => {
                    const val = [...this.value]
                    val.splice(e.newIndex, 0, val.splice(e.oldIndex, 1)[0])
                    this.update(val)
                })
        },

        itemCreated(item) {
            this.$emit('item-data-updated', [...this.data, item])
            this.update([...this.value, item.id])
        },

        selectFieldSelected(selectedItemData) {
            this.$emit(
                'item-data-updated',
                selectedItemData.map(item => ({
                    id: item.id,
                    title: item.title,
                }))
            )
            this.update(selectedItemData.map(item => item.id))
        },

        setLoadingProgress(state) {
            this.$progress.loading(`relationship-fieldtype-${this._uid}`, state)
        },
    },
}
</script>
