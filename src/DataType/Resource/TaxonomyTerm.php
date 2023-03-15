<?php
namespace Taxonomy\DataType\Resource;

use Omeka\DataType\ValueAnnotatingInterface;
use Omeka\DataType\Resource\AbstractResource;
use Taxonomy\Entity;
use Taxonomy\Api\Representation\TaxonomyRepresentation;
use Laminas\View\Renderer\PhpRenderer;

class TaxonomyTerm extends AbstractResource implements ValueAnnotatingInterface
{
    protected $taxonomy;

    public function __construct(TaxonomyRepresentation $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function getOptgrouplabel()
    {
        return 'Taxonomy';
    }

    public function getName()
    {
        return 'resource:taxonomy-term:' . $this->taxonomy->code();
    }

    public function getLabel()
    {
        return sprintf('Taxonomy Term: %s', $this->taxonomy->displayTitle()); // @translate
    }

    public function form(PhpRenderer $view)
    {
        return $view->partial('taxonomy/common/data-type/taxonomy-term', [
            'dataType' => $this->getName(),
            'resource' => $view->resource,
            'taxonomy' => $this->taxonomy,
        ]);
    }

    public function valueAnnotationPrepareForm(PhpRenderer $view)
    {
    }

    public function valueAnnotationForm(PhpRenderer $view)
    {
        return $view->partial('taxonomy/common/data-type/value-annotation-taxonomy-term', [
            'taxonomy' => $this->taxonomy,
        ]);
    }

    public function getValidValueResources()
    {
        return [Entity\TaxonomyTerm::class];
    }
}
