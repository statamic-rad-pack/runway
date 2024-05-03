import PublishForm from './components/Publish/PublishForm.vue'
import RunwayListing from './components/Listing/RunwayListing.vue'
import HasManyFieldtypeIndex from '../../vendor/statamic/cms/resources/js/components/fieldtypes/relationship/RelationshipIndexFieldtype.vue'

Statamic.$components.register('runway-publish-form', PublishForm)
Statamic.$components.register('runway-listing', RunwayListing)
Statamic.$components.register('has_many-fieldtype-index', HasManyFieldtypeIndex)
