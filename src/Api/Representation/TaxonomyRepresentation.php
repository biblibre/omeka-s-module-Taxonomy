<?php
namespace Taxonomy\Api\Representation;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class TaxonomyRepresentation extends AbstractResourceEntityRepresentation
{
    public function getControllerName()
    {
        return 'taxonomy';
    }

    public function getResourceJsonLdType()
    {
        return 'o-module-Taxonomy:Taxonomy';
    }

    public function getResourceJsonLd()
    {
        $url = $this->getViewHelper('Url');
        $termsUrl = $url(
            'api/default',
            ['resource' => 'taxonomy-terms'],
            [
                'force_canonical' => true,
                'query' => ['taxonomy_id' => $this->id()],
            ]
        );

        return [
            'o:code' => $this->code(),
            'o:terms' => ['@id' => $termsUrl],
        ];
    }

    /**
     * Get this taxonomy's term count.
     *
     * @return int
     */
    public function termCount()
    {
        $response = $this->getServiceLocator()->get('Omeka\ApiManager')
            ->search('taxonomy_terms', [
                'taxonomy_id' => $this->id(),
                'limit' => 0,
            ]);

        return $response->getTotalResults();
    }

    /**
     * Get taxonomy's code
     *
     * @return string
     */
    public function code()
    {
        return $this->resource->getCode();
    }

    public function siteUrl($siteSlug = null, $canonical = false)
    {
        if (!$siteSlug) {
            $siteSlug = $this->getServiceLocator()->get('Application')
                ->getMvcEvent()->getRouteMatch()->getParam('site-slug');
        }
        $url = $this->getViewHelper('Url');

        return $url(
            'site/taxonomy-id',
            [
                'site-slug' => $siteSlug,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function termsUrl($action = 'browse', $canonical = false)
    {
        $url = $this->getViewHelper('Url');

        return $url(
            'admin/taxonomy-term',
            ['action' => $action],
            [
                'query' => ['taxonomy_id' => $this->id()],
                'force_canonical' => $canonical,
            ]
        );
    }

    public function subjectValueTotalCount($property = null)
    {
        return $this->getAdapter()->getSubjectValueTotalCount($this->resource, $property);
    }

    public function valueRepresentation()
    {
        $valueRepresentation = parent::valueRepresentation();

        $valueRepresentation['code'] = $this->code();

        return $valueRepresentation;
    }
}
