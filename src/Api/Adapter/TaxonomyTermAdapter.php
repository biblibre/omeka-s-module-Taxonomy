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

        if (isset($query['code'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.code',
                $this->createNamedParameter($qb, $query['code'])
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
     * @param Resource $resource
     * @param int|null $property
     * @return QueryBuilder
     */
    public function getSubjectValuesQueryBuilder(Resource $resource, $property = null, $resourceType = null, $siteId = null)
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
