<template>
    <div>
        <breadcrumb v-if="breadcrumbs" :url="breadcrumbs[0].url" :title="breadcrumbs[0].text" />

        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-html="$options.filters.striptags(title)" />
                </div>
            </h1>

            <dropdown-list class="rtl:ml-4 ltr:mr-4" v-if="canEditBlueprint">
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

            <div class="pt-px text-2xs text-gray-600 flex rtl:ml-4 ltr:mr-4" v-if="readOnly">
                <svg-icon name="light/lock" class="w-4 rtl:ml-1 ltr:mr-1 -mt-1" /> {{ __('Read Only') }}
            </div>

            <div v-if="!readOnly" class="hidden md:flex items-center">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!revisionsEnabled && !isInline"
                    :button-class="saveButtonClass"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        :class="saveButtonClass"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="saveText"
                    >
                    </button>
                </save-button-options>

                <button
                    v-if="revisionsEnabled && !isCreating"
                    class="rtl:mr-4 ltr:ml-4 btn-primary flex items-center"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true"
                    v-text="this.publishButtonText"
                />
            </div>

            <slot name="action-buttons-right" />
        </div>

        <publish-container
            ref="container"
            :name="publishContainer"
            :blueprint="blueprint"
            :values="values"
            :reference="initialReference"
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
                    :enable-sidebar="sidebarEnabled"
                    @updated="setFieldValue"
                    @meta-updated="setFieldMeta"
                    @focus="$refs.container.$emit('focus', $event)"
                    @blur="$refs.container.$emit('blur', $event)"
                >
                    <template #actions="{ shouldShowSidebar }">
                        <div class="card p-0 mb-5">
                            <div v-if="resourceHasRoutes">
                                <div class="p-3 flex items-center space-x-2" v-if="showVisitUrlButton">
                                    <a
                                        class="flex items-center justify-center btn w-full"
                                        v-if="showVisitUrlButton"
                                        :href="permalink"
                                        target="_blank">
                                        <svg-icon name="light/external-link" class="w-4 h-4 rtl:ml-2 ltr:mr-2 shrink-0" />
                                        <span>{{ __('Visit URL') }}</span>
                                    </a>
                                </div>
                            </div>

                            <div v-if="publishStatesEnabled && !revisionsEnabled">
                                <div
                                    class="flex items-center justify-between px-4 py-2"
                                    :class="{ 'border-t dark:border-dark-900': resourceHasRoutes && permalink }"
                                >
                                    <label v-text="__('Published')" class="publish-field-label font-medium" />
                                    <toggle-input :value="published" :read-only="!canManagePublishState" @input="setFieldValue(resource.published_column, $event)" />
                                </div>
                            </div>

                            <div
                                v-if="revisionsEnabled && !isCreating"
                                class="p-4"
                                :class="{ 'border-t dark:border-dark-900': showVisitUrlButton }"
                            >
                                <label class="publish-field-label font-medium mb-2" v-text="__('Revisions')"/>
                                <div class="mb-1 flex items-center" v-if="published">
                                    <span class="text-green-600 w-6 text-center">&check;</span>
                                    <span class="text-2xs" v-text="__('Model has a published version')"></span>
                                </div>
                                <div class="mb-1 flex items-center" v-else>
                                    <span class="text-orange w-6 text-center">!</span>
                                    <span class="text-2xs" v-text="__('Model has not been published')"></span>
                                </div>
                                <div class="mb-1 flex items-center" v-if="!isWorkingCopy && published">
                                    <span class="text-green-600 w-6 text-center">&check;</span>
                                    <span class="text-2xs" v-text="__('This is the published version')"></span>
                                </div>
                                <div class="mb-1 flex items-center" v-if="isDirty">
                                    <span class="text-orange w-6 text-center">!</span>
                                    <span class="text-2xs" v-text="__('Unsaved changes')"></span>
                                </div>
                                <button
                                    class="flex items-center justify-center mt-4 btn-flat px-2 w-full"
                                    v-if="!isCreating && revisionsEnabled"
                                    @click="showRevisionHistory = true">
                                    <svg-icon name="light/history" class="h-4 w-4 rtl:ml-2 ltr:mr-2" />
                                    <span>{{ __('View History') }}</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </publish-tabs>
            </div>

            <template v-slot:buttons>
                <button
                    v-if="!readOnly"
                    class="rtl:mr-4 ltr:ml-4"
                    :class="saveButtonClass"
                    :disabled="!canSave"
                    @click.prevent="save"
                    v-text="saveText">
                </button>

                <button
                    v-if="revisionsEnabled && !isCreating"
                    class="rtl:mr-4 ltr:ml-4 btn-primary flex items-center"
                    :disabled="!canPublish"
                    @click="confirmingPublish = true">
                    <span v-text="this.publishButtonText" />
                    <svg-icon name="micro/chevron-down-xs" class="rtl:mr-2 ltr:ml-2 w-2" />
                </button>
            </template>
        </publish-container>

        <div class="md:hidden mt-6 flex items-center">
            <button
                v-if="!readOnly"
                class="btn-lg"
                :class="{
                    'btn-primary w-full': ! revisionsEnabled,
                    'btn w-1/2 rtl:ml-4 ltr:mr-4': revisionsEnabled,
                }"
                :disabled="!canSave"
                @click.prevent="save"
                v-text="__(revisionsEnabled ? 'Save Changes' : 'Save')" />

            <button
                v-if="revisionsEnabled"
                class="rtl:mr-2 ltr:ml-2 btn btn-lg justify-center btn-primary flex items-center w-1/2"
                :disabled="!canPublish"
                @click="confirmingPublish = true">
                <span v-text="this.publishButtonText" />
                <svg-icon name="micro/chevron-down-xs" class="rtl:mr-2 ltr:ml-2 w-2" />
            </button>
        </div>

        <stack name="revision-history" v-if="showRevisionHistory" @closed="showRevisionHistory = false" :narrow="true">
            <revision-history
                slot-scope="{ close }"
                :index-url="actions.revisions"
                :restore-url="actions.restore"
                :reference="initialReference"
                @closed="close"
            />
        </stack>

        <publish-actions
            v-if="confirmingPublish"
            :actions="actions"
            :published="published"
            :resource-handle="resource.handle"
            :reference="initialReference"
            :publish-container="publishContainer"
            :can-manage-publish-state="canManagePublishState"
            @closed="confirmingPublish = false"
            @saving="saving = true"
            @saved="publishActionCompleted"
        />
    </div>
</template>

<script>
import axios from 'axios'
import SaveButtonOptions from '../../../../vendor/statamic/cms/resources/js/components/publish/SaveButtonOptions.vue'
import HasPreferences from '../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences.js'
import HasHiddenFields from '../../../../vendor/statamic/cms/resources/js/components/publish/HasHiddenFields.js'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions.js'
import RevisionHistory from '../revision-history/History.vue'
import PublishActions from './PublishActions.vue'

export default {
    components: {
        SaveButtonOptions,
        RevisionHistory,
        PublishActions,
    },

    mixins: [HasPreferences, HasHiddenFields, HasActions],

    props: {
        publishContainer: String,
        initialReference: String,
        initialBlueprint: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialIsWorkingCopy: Boolean,
        resource: Object,
        breadcrumbs: Array,
        initialActions: Object,
        method: String,
        isCreating: Boolean,
        isInline: Boolean,
        initialReadOnly: Boolean,
        initialPermalink: String,
        revisionsEnabled: Boolean,
        canEditBlueprint: Boolean,
        canManagePublishState: Boolean,
        createAnotherUrl: String,
        initialListingUrl: String,
        resourceHasRoutes: Boolean,
    },

    data() {
        return {
            actions: this.initialActions,
            saving: false,
            trackDirtyState: true,
            blueprint: this.initialBlueprint,
            title: this.initialTitle,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            isWorkingCopy: this.initialIsWorkingCopy,
            error: null,
            errors: {},
            state: 'new',
            revisionMessage: null,
            showRevisionHistory: null,
            preferencesPrefix: `runway.${this.resource.handle}`,

            // Whether it was published the last time it was saved.
            // Successful publish actions (if using revisions) or just saving (if not) will update this.
            // The current published value is inside the "values" object, and also accessible as a computed.
            initialPublished: this.initialValues[this.resource.published_column],

            confirmingPublish: false,
            readOnly: this.initialReadOnly,
            permalink: this.initialPermalink,

            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
        }
    },

    computed: {
        somethingIsLoading() {
            return ! this.$progress.isComplete();
        },

        canSave() {
            return !this.readOnly && !this.somethingIsLoading;
        },

        canPublish() {
            if (!this.revisionsEnabled) return false;

            if (this.readOnly || this.isCreating || this.somethingIsLoading || this.isDirty) return false;

            return true;
        },

        publishStatesEnabled() {
            return this.resource.has_publish_states;
        },

        published() {
            if (! this.publishStatesEnabled) return false;

            return this.values[this.resource.published_column];
        },

        listingUrl() {
            return `${this.initialListingUrl}`;
        },

        showVisitUrlButton() {
            return !!this.permalink;
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        publishButtonText() {
            if (this.canManagePublishState) {
                return `${__('Publish')}…`
            }

            return `${__('Create Revision')}…`
        },

        saveText() {
            switch(true) {
                case this.revisionsEnabled:
                    return __('Save Changes');
                case this.isUnpublishing:
                    return __('Save & Unpublish');
                case this.publishStatesEnabled && this.isDraft:
                    return __('Save Draft');
                default:
                    return this.publishStatesEnabled
                        ? __('Save & Publish')
                        : __('Save');
            }
        },

        isUnpublishing() {
            if (! this.publishStatesEnabled) return false;

            return this.initialPublished && ! this.published && ! this.isCreating;
        },

        isDraft() {
            if (! this.publishStatesEnabled) return false;

            return ! this.published;
        },

        saveButtonClass() {
            return {
                'btn': this.revisionsEnabled,
                'btn-primary': this.isCreating || !this.revisionsEnabled,
            };
        },

        afterSaveOption() {
            return this.getPreference('after_save')
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },

        sidebarEnabled() {
            let hasSidebarTab = this.blueprint.tabs.filter((tab) => tab.handle === 'sidebar').length > 0

            return this.resourceHasRoutes || this.publishStatesEnabled || hasSidebarTab
        },
    },

    watch: {
        saving(saving) {
            this.$progress.loading(`runway-publish-form`, saving)
        },

        title(title) {
            if (this.isBase) {
                const arrow = this.direction === 'ltr' ? '‹' : '›';
                document.title = `${title} ${arrow} ${this.breadcrumbs[0].text} ${arrow} ${__('Statamic')}`;
            }
        },
    },

    methods: {
        clearErrors() {
            this.error = null
            this.errors = {}
        },

        save() {
            if (! this.canSave) {
                this.quickSave = false;
                return;
            }

            this.saving = true;
            this.clearErrors();

            setTimeout(() => this.runBeforeSaveHook(), 151); // 150ms is the debounce time for fieldtype updates
        },

        runBeforeSaveHook() {
            this.$refs.container.saving();

            Statamic.$hooks.run('runway.saving', {
                resource: this.resource,
                values: this.values,
                container: this.$refs.container,
                storeName: this.publishContainer,
            })
                .then(this.performSaveRequest)
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(error || 'Something went wrong');
                });
        },

        performSaveRequest() {
            // Once the hook has completed, we need to make the actual request.
            // We build the payload here because the before hook may have modified values.
            const payload = { ...this.visibleValues};

            axios({
                method: this.method,
                url: this.actions.save,
                data: payload,
            }).then(response => {
                this.saving = false;
                if (! response.data.saved) {
                    return this.$toast.error(__(`Couldn't save entry`));
                }
                this.title = response.data.data.title;
                this.isWorkingCopy = true;
                if (!this.revisionsEnabled) this.permalink = response.data.data.permalink;
                if (!this.isCreating) this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.runAfterSaveHook(response);
            }).catch(error => this.handleAxiosError(error));
        },

        runAfterSaveHook(response) {
            // Once the save request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('runway.saved', {
                    resource: this.resource,
                    reference: this.initialReference,
                    response
                })
                .then(() => {
                    // If revisions are enabled, just emit event.
                    if (this.revisionsEnabled) {
                        clearTimeout(this.trackDirtyStateTimeout)
                        this.trackDirtyState = false
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350)
                        this.$nextTick(() => this.$emit('saved', response));
                        return;
                    }

                    let nextAction = this.quickSave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another entry, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        window.location = this.createAnotherUrl;
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    else if (!this.isInline && nextAction === null) {
                        window.location = this.listingUrl;
                    }

                    // Otherwise, leave them on the edit form and emit an event. We need to wait until after
                    // the hooks are resolved because if this form is being shown in a stack, we only
                    // want to close it once everything's done.
                    else {
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350);

                        if (this.publishStatesEnabled) {
                            this.initialPublished = response.data.data.published;
                        }

                        this.$nextTick(() => this.$emit('saved', response));

                        if (!this.isInline && this.isCreating) {
                            window.location = response.data.data.edit_url + '?created=true';
                        }
                    }

                    this.quickSave = false;
                }).catch(e => console.error(e));
        },

        confirmPublish() {
            if (this.canPublish) {
                this.confirmingPublish = true;
            }
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
                this.$reveal.invalid();
            } else if (e.response) {
                this.$toast.error(e.response.data.message);
            } else {
                this.$toast.error(e || 'Something went wrong');
            }
        },

        publishActionCompleted({ published, isWorkingCopy, response }) {
            this.saving = false;
            if (published !== undefined) {
                this.$refs.container.setFieldValue('published', published);
                this.initialPublished = published;
            }
            this.$refs.container.saved();
            this.isWorkingCopy = isWorkingCopy;
            this.confirmingPublish = false;
            this.title = response.data.data.title;
            this.values = this.resetValuesFromResponse(response.data.data.values);
            this.permalink = response.data.data.permalink
            this.$nextTick(() => this.$emit('saved', response));
        },

        setFieldValue(handle, value) {
            this.$refs.container.setFieldValue(handle, value)
        },

        setFieldMeta(handle, value) {
            this.$store.dispatch(`publish/${this.publishContainer}/setFieldMeta`, {
                handle,
                value,
                user: Statamic.user.id,
            })
        },

        /**
         * When creating a new model via the HasMany fieldtype, pre-fill the belongs_to field to the current model.
         */
        prefillBelongsToField() {
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
                if (!this.revisionsEnabled) this.permalink = response.data.permalink;
                this.values = this.resetValuesFromResponse(response.data.values);
                if (this.publishStatesEnabled) {
                    this.initialPublished = response.data.published;
                }
                this.itemActions = response.data.itemActions;
            }
        },
    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.quickSave = true;
            this.save();
        });
    },

    created() {
        if (this.publishContainer.includes('relate-fieldtype-inline')) {
            this.prefillBelongsToField()
        }
    },

    unmounted() {
        clearTimeout(this.trackDirtyStateTimeout);
    },

    destroyed() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    },
}
</script>
