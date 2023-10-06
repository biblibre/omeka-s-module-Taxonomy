<?php
namespace Taxonomy\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractResourceEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Resource;
use Omeka\Stdlib\ErrorStore;
use Taxonomy\Api\Representation\TaxonomyTermRepresentation;
use Taxonomy\Entity\TaxonomyTerm;

class TaxonomyTermAdapter extends AbstractResourceEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'code' => 'code',
        'created' => 'created',
        'modified' => 'modified',
        'title' => 'title',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'code' => 'code',
        'title' => 'title',
        'created' => 'created',
        'modified' => 'modified',
        'is_public' => 'isPublic',
        'thumbnail' => 'thumbnail',
    ];

    public function getResourceName()
    {
        return 'taxonomy_terms';
    }

    public function getRepresentationClass()
    {
        return TaxonomyTermRepresentation::class;
    }

    public function getEntityClass()
    {
        return TaxonomyTerm::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        parent::buildQuery($qb, $query);

        if (isset($query['code']) && is_string($query['code']) && $query['code'] !== '') {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.code',
                $this->createNamedParameter($qb, $query['code'])
            ));
        }

        if (array_key_exists('parent_id', $query)) {
            if ($query['parent_id'] === null) {
                $qb->andWhere($qb->expr()->isNull('omeka_root.parent'));
            } else {
                $qb->andWhere($qb->expr()->eq(
                    'omeka_root.parent',
                    $this->createNamedParameter($qb, $query['parent_id'])
                ));
            }
        }

        $exclude_ids = [];
        if (isset($query['exclude_ids'])) {
            if (is_string($query['exclude_ids'])) {
                $exclude_ids = explode(',', $query['exclude_ids']);
            } elseif (is_array($query['exclude_ids'])) {
                $exclude_ids = $query['exclude_ids'];
            } else {
                $exclude_ids = [$query['exclude_ids']];
            }
        }

        if (isset($query['exclude_children_of'])) {
            $connection = $this->getEntityManager()->getConnection();
            $children_ids = $connection->fetchFirstColumn(
                'WITH RECURSIVE child (id) as (select id from taxonomy_term where parent_id = ? union all select t.id from child c join taxonomy_term t on t.parent_id = c.id) select id from child',
                [$query['exclude_children_of']]
            );
            $exclude_ids = array_merge($exclude_ids, $children_ids);
        }

        if (!empty($exclude_ids)) {
            $qb->andWhere($qb->expr()->notIn(
                'omeka_root.id',
                $this->createNamedParameter($qb, $exclude_ids),
            ));
        }

        if (isset($query['taxonomy_id']) && is_numeric($query['taxonomy_id'])) {
            $taxonomyAlias = $this->createAlias();
            $qb->innerJoin(
                'omeka_root.taxonomy',
                $taxonomyAlias
            );
            $qb->andWhere($qb->expr()->eq(
                "$taxonomyAlias.id",
                $this->createNamedParameter($qb, $query['taxonomy_id']))
            );
        }
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        parent::hydrate($request, $entity, $errorStore);

        $data = $request->getContent();

        if (Request::CREATE === $request->getOperation() && isset($data['o:code'])) {
            $entity->setCode($data['o:code']);
        }

        if (Request::CREATE === $request->getOperation() && isset($data['o:taxonomy']['o:id'])) {
            $taxonomy = $this->getAdapter('taxonomies')->findEntity($data['o:taxonomy']['o:id']);
            $this->authorize($taxonomy, 'add-term');
            $entity->setTaxonomy($taxonomy);
        }

        if ($this->shouldHydrate($request, 'o:parent')) {
            try {
                $parent = $this->getAdapter('taxonomy_terms')->findEntity($request->getValue('o:parent'));
            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                $parent = null;
            }
            $entity->setParent($parent);
        }
    }

    /**
     * Get the query builder needed to get subject values.
     *
     * Note that the returned query builder does not include $qb->select().
     *
     * The $propertyId argument has three variations, depending on the desired
     * result:
     *
     * - <property-id>: Query all subject values of the specified property, e.g.
     *      123
     * - <property-id>-: Query subject values of the specified property where
     *      there is no corresponding resource template property, e.g. 123-
     * - <property-id>-<resource-template-property-id>: Query subject values of
     *      the specified property where there is a corresponding resource
     *      template property, e.g. 123-234
     *
     * @param Resource $resource
     * @param int|string|null $propertyId
     * @param string|null $resourceType
     * @param int|null $siteId
     * @return QueryBuilder
     */
    public function getSubjectValuesQueryBuilder(Resource $resource, $propertyId = null, $resourceType = null, $siteId = null)
    {
        if (version_compare(\Omeka\Module::VERSION, '4.0', '<')) {
            return $this->getSubjectValuesQueryBuilderV3($resource, $propertyId);
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->from('Omeka\Entity\Value', 'value')
            ->join('value.resource', 'resource')
            ->leftJoin('resource.resourceTemplate', 'resource_template')
            ->leftJoin('resource_template.resourceTemplateProperties', 'resource_template_property', 'WITH', 'value.property = resource_template_property.property')
            ->where($qb->expr()->eq('value.valueResource', $this->createNamedParameter($qb, $resource)));
        // Filter according to resource type and site. Note that we can only
        // filter by site when a resource type is passed because each resource
        // type requires joins that are mutually incompatible.
        switch ($resourceType) {
            case 'item_sets':
                $qb->andWhere('resource INSTANCE OF Omeka\Entity\ItemSet');
                if ($siteId) {
                    $qb->join('Omeka\Entity\SiteItemSet', 'site_item_set', 'WITH', 'resource.id = site_item_set.itemSet')
                        ->andWhere($qb->expr()->eq('site_item_set.site', $siteId));
                }
                break;
            case 'media':
                $qb->andWhere('resource INSTANCE OF Omeka\Entity\Media');
                if ($siteId) {
                    $qb->join('Omeka\Entity\Media', 'media', 'WITH', 'resource.id = media.id')
                        ->join('media.item', 'item')
                        ->join('item.sites', 'site')
                        ->andWhere($qb->expr()->eq('site.id', $siteId));
                }
                break;
            case 'items':
                $qb->andWhere('resource INSTANCE OF Omeka\Entity\Item');
                if ($siteId) {
                    $qb->join('Omeka\Entity\Item', 'item', 'WITH', 'resource.id = item.id')
                        ->join('item.sites', 'site')
                        ->andWhere($qb->expr()->eq('site.id', $siteId));
                }
                break;
            case 'taxonomies':
                $qb->andWhere('resource INSTANCE OF Taxonomy\Entity\Taxonomy');
                break;
            case 'taxonomy_terms':
                $qb->andWhere('resource INSTANCE OF Taxonomy\Entity\TaxonomyTerm');
                break;
            default:
                $qb->andWhere($qb->expr()->orX(
                    'resource INSTANCE OF Omeka\Entity\Item',
                    'resource INSTANCE OF Omeka\Entity\ItemSet',
                    'resource INSTANCE OF Omeka\Entity\Media',
                    'resource INSTANCE OF Taxonomy\Entity\Taxonomy',
                    'resource INSTANCE OF Taxonomy\Entity\TaxonomyTerm'
                ));
        }
        // Filter by property and resource template property.
        if ($propertyId) {
            if (false !== strpos($propertyId, '-')) {
                $propertyIds = explode('-', $propertyId);
                $propertyId = $propertyIds[0];
                $resourceTemplatePropertyId = $propertyIds[1];
                $qb->andWhere($resourceTemplatePropertyId
                    ? $qb->expr()->eq('resource_template_property', $this->createNamedParameter($qb, $resourceTemplatePropertyId))
                    : $qb->expr()->isNull('resource_template_property')
                );
            }
            $qb->andWhere($qb->expr()->eq('value.property', $this->createNamedParameter($qb, $propertyId)));
        }
        // Need to check visibility manually here
        $services = $this->getServiceLocator();
        $acl = $services->get('Omeka\Acl');
        $identity = $services->get('Omeka\AuthenticationService')->getIdentity();
        if (!$acl->userIsAllowed('Omeka\Entity\Resource', 'view-all')) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('resource.isPublic', '1'),
                $qb->expr()->eq('resource.owner', $this->createNamedParameter($qb, $identity))
            ));
        }
        return $qb;
    }

    protected function getSubjectValuesQueryBuilderV3(Resource $resource, $property = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->from('Omeka\Entity\Value', 'v')
            ->join('v.resource', 'r')
            ->where($qb->expr()->eq('v.valueResource', $this->createNamedParameter($qb, $resource)))
            // Limit subject values to those belonging to primary resources.
            ->andWhere($qb->expr()->orX(
                'r INSTANCE OF Omeka\Entity\Item',
                'r INSTANCE OF Omeka\Entity\ItemSet',
                'r INSTANCE OF Omeka\Entity\Media',
                'r INSTANCE OF Taxonomy\Entity\Taxonomy',
                'r INSTANCE OF Taxonomy\Entity\TaxonomyTerm'
            ));
        if ($property) {
            $qb->andWhere($qb->expr()->eq('v.property', $this->createNamedParameter($qb, $property)));
        }
        // Need to check visibility manually here
        $services = $this->getServiceLocator();
        $acl = $services->get('Omeka\Acl');
        $identity = $services->get('Omeka\AuthenticationService')->getIdentity();
        if (!$acl->userIsAllowed('Omeka\Entity\Resource', 'view-all')) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('r.isPublic', '1'),
                $qb->expr()->eq('r.owner', $this->createNamedParameter($qb, $identity))
            ));
        }
        return $qb;
    }
}
