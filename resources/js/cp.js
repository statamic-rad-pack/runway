import RelationshipFieldtype from './components/Fieldtypes/Relationship/RelationshipFieldtype.vue'
import RelationshipTableMode from './components/Fieldtypes/Relationship/RelationshipTableMode.vue'
import HasManyRelatedItem from './components/Fieldtypes/HasManyRelatedItem.vue'
import PublishForm from './components/Publish/PublishForm.vue'
import ListingView from './components/Listing/ListingView.vue'

Statamic.$components.register(
    'runway-relationship-fieldtype',
    RelationshipFieldtype
)
Statamic.$components.register('relationship-table-mode', RelationshipTableMode)
Statamic.$components.register('hasmany-related-item', HasManyRelatedItem)
Statamic.$components.register('runway-publish-form', PublishForm)
Statamic.$components.register('runway-listing-view', ListingView)

// Statamic seems to form the fieldtype name differently, so we're just pointing it where it should be going so stuff works.
import HasManyFieldtypeIndex from '../../vendor/statamic/cms/resources/js/components/fieldtypes/relationship/RelationshipIndexFieldtype.vue'

Statamic.$components.register('has_many-fieldtype-index', HasManyFieldtypeIndex)
