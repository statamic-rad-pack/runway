<script>
/**
 * This component extends the RevisionHistory component included in Statamic Core,
 * allowing us to import our own version of the Revision component.
 */

import Revision from './Revision.vue';
import RevisionHistory from '../../../../vendor/statamic/cms/resources/js/components/revision-history/History.vue';

export default {
    components: {
        Revision,
    },

    mixins: [RevisionHistory],
};
</script>

