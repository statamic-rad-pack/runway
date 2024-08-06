<template>
    <statamic-inline-actions
        :item="item"
        :url="url"
        :actions="actions"
        @completed="actionCompleted"
    ></statamic-inline-actions>
</template>

<script>
import StatamicInlineActions from '../../../../vendor/statamic/cms/resources/js/components/data-list/InlineActions.vue'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions.js'

export default {

    mixins: [HasActions],

    components: {
        StatamicInlineActions
    },

    props: {
        actions: Array,
        item: { required: true },
        url: String
    },
}
</script>
