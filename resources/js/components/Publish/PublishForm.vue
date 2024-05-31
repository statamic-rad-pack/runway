<template>
    <div>
        <breadcrumb
            v-if="breadcrumbs"
            :url="breadcrumbs[0].url"
            :title="breadcrumbs[0].text"
        />

        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-html="title" />
                </div>
            </h1>

            <dropdown-list class="mr-4" v-if="canEditBlueprint">
                <dropdown-item :text="__('Edit Blueprint')" :redirect="actions.editBlueprint" />
                <li class="divider" />
                <data-list-inline-actions
                    v-if="!isCreating"
                    :item="values.id"
                    :url="itemActionUrl"
                    :actions="itemActions"
                    :is-dirty="isDirty"
                    @started="actionStarted"
                    @completed="actionCompleted"
                />
            </dropdown-list>

            <div class="flex pt-px mr-4 text-gray-600 text-2xs" v-if="readOnly">
                <svg-icon name="light/lock" class="w-4 mr-1 -mt-1" /> {{ __('Read Only') }}
            </div>

            <div class="flex items-center mt-6">
                <button
                    v-if="!readOnly"
                    class="btn-lg"
                    :class="{
                        'btn-primary w-full': ! revisionsEnabled,
                        'btn w-1/2 rtl:ml-4 ltr:mr-4': revisionsEnabled,
                    }"
                    @click.prevent="save"
                    v-text="__(revisionsEnabled ? 'Save Changes' : 'Save')"
                >
                </button>

                <button
                    v-if="revisionsEnabled"
                    class="flex items-center justify-center w-1/2 rtl:mr-2 ltr:ml-2 btn btn-lg btn-primary"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true">
                    <span v-text="__('Publish')" />
                    <svg-icon name="micro/chevron-down-xs" class="w-2 rtl:mr-2 ltr:ml-2" />
                </button>
            </div>

            <div v-if="!readOnly" class="items-center hidden md:flex">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!isInline"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        class="btn-primary"
                        @click.prevent="save"
                    >
                        {{ __('Save') }}
                    </button>
                </save-button-options>
            </div>

            <slot name="action-buttons-right" />
        </div>

        <div class="p-4">
            <revision
                :isDirty="false"
                :isWorkingCopy="true"
                :published="true"
                @show-history="showRevisionHistory = true"
            ></revision>
        </div>
        <publish-container
            ref="container"
            :name="publishContainer"
            :reference="initialReference"
            :blueprint="blueprint"
            :values="values"
            :meta="meta"
            :errors="errors"
            @updated="values = $event"
        >
            <div>
                <component
                    v-for="component in components"
                    :key="component.id"
                    :is="component.name"
                    :container="container"
                    v-bind="component.props"
                    v-on="component.events"
                />

                <publish-tabs
                    :read-only="readOnly"
                    :enable-sidebar="shouldShowSidebar"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @focus="$refs.container.$emit('focus', $event)"
                    @blur="$refs.container.$emit('blur', $event)"
                >
                    <template #actions="{ shouldShowSidebar }">
                        <div v-if="shouldShowSidebar" class="card p-0" :class="{ 'mb-5': resourceHasRoutes && permalink }">
                            <div
                                v-if="resourceHasRoutes && permalink"
                                :class="{ hi: !shouldShowSidebar }"
                            >
                                <div class="flex items-center p-3 space-x-2">
                                    <a
                                        class="flex items-center justify-center w-full btn"
                                        v-if="permalink"
                                        :href="permalink"
                                        target="_blank"
                                    >
                                        <svg-icon name="light/external-link" class="w-4 h-4 mr-2 shrink-0" />
                                        <span>{{ __('Visit URL') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </publish-tabs>
            </div>
        </publish-container>

        <div class="flex items-center mt-3 md:hidden">
            <button
                v-if="!readOnly"
                class="w-full btn-lg btn-primary"
                @click.prevent="save"
            >
                {{ __('Save') }}
            </button>
        </div>
    </div>
</template>

<script>
import SaveButtonOptions from '../statamic/SaveButtonOptions.vue'
import HasPreferences from '../statamic/HasPreferences.js'
import Revision from '../statamic/Revision.vue'
import HasHiddenFields from '../../../../vendor/statamic/cms/resources/js/components/publish/HasHiddenFields.js'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions.js'

export default {
    components: {
        Revision,
        SaveButtonOptions,
    },

    mixins: [HasPreferences, HasHiddenFields, HasActions],

    props: {
        breadcrumbs: Array,
        initialReference: String,
        initialActions: Object,
        initialBlueprint: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        method: String,
        resourceHasRoutes: Boolean,
        permalink: String,
        isCreating: {
            type: Boolean,
            default: false,
        },
        isInline: {
            type: Boolean,
            default: false,
        },
        publishContainer: String,
        readOnly: Boolean,
        resource: {
            type: Object,
            required: true,
        },
        createAnotherUrl: String,
        listingUrl: String,
        canEditBlueprint: Boolean,
        revisionsEnabled: Boolean,
        canPublish: Boolean,
    },

    data() {
        return {
            actions: this.initialActions,
            blueprint: this.initialBlueprint,
            values: this.initialValues,
            meta: this.initialMeta,
            title: this.initialTitle,
            preferencesPrefix: `runway.${this.resource.handle}`,

            errors: {},
            saving: false,
            containerWidth: null,
            saveKeyBinding: null,
            quickSave: false,
        }
    },

    computed: {
        enableSidebar() {
            return this.blueprint.tabs
                .map((section) => section.handle)
                .includes('sidebar')
        },

        shouldShowSidebar() {
            return this.enableSidebar
        },

        afterSaveOption() {
            return this.getPreference('after_save')
        },
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(
            ['mod+s', 'mod+return'],
            (e) => {
                e.preventDefault()
                this.quickSave = true
                this.save()
            }
        )
    },

    created() {
        if (this.publishContainer.includes('relate-fieldtype-inline')) {
            this.prefillBelongsToField()
        }
    },

    methods: {
        save() {
            this.saving = true
            this.clearErrors()

            this.$axios({
                method: this.method,
                url: this.actions.save,
                data: this.values,
            })
                .then((response) => {
                    this.saving = false
                    this.$refs.container.saved()
                    this.$emit('saved', response)

                    let nextAction = this.quickSave
                        ? 'continue_editing'
                        : this.afterSaveOption

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        this.$nextTick(() => {
                            window.location = this.createAnotherUrl
                        })

                        return
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    if (!this.isInline && nextAction === null) {
                        this.$nextTick(() => {
                            window.location = this.listingUrl
                        })

                        return
                    }

                    // Otherwise, leave them on the edit form (or redirect them to the edit form if they're creating a new model).
                    if (this.isCreating && this.publishContainer === 'base') {
                        this.$nextTick(() => {
                            window.location.href = response.data.redirect
                        })
                    } else {
                        this.quickSave = false
                        this.$toast.success(__('Saved'))
                        this.$nextTick(() => this.$emit('saved', response));
                    }
                })
                .catch((error) => this.handleAxiosError(error))
        },

        clearErrors() {
            this.error = null
            this.errors = {}
        },

        handleAxiosError(e) {
            this.saving = false

            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data
                this.error = message
                this.errors = errors
                this.$toast.error(message)
            } else if (e.response) {
                this.$toast.error(e.response.data.message)
            } else {
                this.$toast.error(e || 'Something went wrong')
            }
        },

        setFieldValue(handle, value) {
            this.$refs.container.setFieldValue(handle, value)
        },

        setFieldMeta(handle, value) {
            this.$store.dispatch(
                `publish/${this.publishContainer}/setFieldMeta`,
                {
                    handle,
                    value,
                    user: Statamic.user.id,
                }
            )
        },

        /**
         * When creating a new model via the HasMany fieldtype, pre-fill the belongs_to field to the current model.
         */
        prefillBelongsToField() {
            this.values['from_inline_publish_form'] = true

            this.initialBlueprint.tabs.forEach((tab) => {
                tab.sections.forEach((section) => {
                    section.fields
                        .filter((field) => {
                            return field.type === 'belongs_to' && field.resource === window.Runway.currentResource;
                        })
                        .forEach((field) => {
                            let alreadyExists = this.values[field.handle].includes(window.Runway.currentModel.id)

                            if (!alreadyExists) {
                                this.values[field.handle].push(window.Runway.currentModel.id)
                                this.meta[field.handle].data = [window.Runway.currentModel]
                            }
                        })
                })
            })
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.values = this.resetValuesFromResponse(response.data.values);
                this.itemActions = response.data.itemActions;
            }
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`runway-publish-form`, saving)
        },
    },

    destroyed() {
        this.saveKeyBinding.destroy()
    },
}
</script>
