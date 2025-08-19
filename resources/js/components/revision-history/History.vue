<script>
/**
 * This component extends the revision-history/History component in Statamic, so
 * we can import our own version of the Revision component.
 */

import Revision from './Revision.vue';
import RevisionHistory from '@statamic/cms';

export default {
    components: {
        Revision,
    },

    mixins: [RevisionHistory],
};
</script>
