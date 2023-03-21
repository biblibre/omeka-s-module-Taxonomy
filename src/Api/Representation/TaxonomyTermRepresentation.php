<?php
namespace Taxonomy\Api\Representation;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class TaxonomyTermRepresentation extends AbstractResourceEntityRepresentation
{
    public function getControllerName()
    {
        return 'taxonomy-term';
    }

    public function getResourceJsonLdType()
    {
        return 'o-module-Taxonomy:TaxonomyTerm';
    }

    public function getResourceJsonLd()
    {
        $taxonomy = $this->taxonomy();

        return [
            'o:code' => $this->code(),
            'o:taxonomy' => $taxonomy->getReference(),
            'taxonomy_code' => $taxonomy->code(),
            'taxonomy_title' => $taxonomy->title(),
        ];
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

    public function taxonomy()
    {
        $taxonomyAdapter = $this->getAdapter('taxonomies');

        return $taxonomyAdapter->getRepresentation($this->resource->getTaxonomy());
    }

    public function siteUrl($siteSlug = null, $canonical = false)
    {
        if (!$siteSlug) {
            $siteSlug = $this->getServiceLocator()->get('Application')
                ->getMvcEvent()->getRouteMatch()->getParam('site-slug');
        }
        $url = $this->getViewHelper('Url');

        return $url(
            'site/taxonomy-term-id',
            [
                'site-slug' => $siteSlug,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function valueRepresentation()
    {
        $valueRepresentation = parent::valueRepresentation();

        $valueRepresentation['code'] = $this->code();

        $taxonomy = $this->taxonomy();
        $valueRepresentation['taxonomy_id'] = $taxonomy->id();
        $valueRepresentation['taxonomy_code'] = $taxonomy->code();
        $valueRepresentation['taxonomy_title'] = $taxonomy->title();

        return $valueRepresentation;
    }
}