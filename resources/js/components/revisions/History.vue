<script>
import Revision from './Revision.vue';
import RevisionHistory from '../../../../vendor/statamic/cms/resources/js/components/revision-history/History.vue';

export default {
    components: {
        Revision,
    },

    mixins: [RevisionHistory],
};
</script>

