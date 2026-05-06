import Index from './pages/Index.vue';
import Create from './pages/Create.vue';
import Edit from './pages/Edit.vue';

import RelatedItem from './components/inputs/relationship/Item.vue';
import PublishForm from './components/PublishForm.vue';
import ResourceWidget from './components/Widget.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('runway::Index', Index);
    Statamic.$inertia.register('runway::Create', Create);
    Statamic.$inertia.register('runway::Edit', Edit);

    Statamic.$components.register('runway-related-item', RelatedItem);
    Statamic.$components.register('runway-publish-form', PublishForm);
    Statamic.$components.register('runway-widget', ResourceWidget);
});
