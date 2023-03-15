<?php
namespace Taxonomy\DataType\Resource;

use Omeka\DataType\ValueAnnotatingInterface;
use Omeka\DataType\Resource\AbstractResource;
use Taxonomy\Entity;
use Laminas\View\Renderer\PhpRenderer;

class Taxonomy extends AbstractResource implements ValueAnnotatingInterface
{
    public function getOptgrouplabel()
    {
        return 'Taxonomy';
    }

    public function getName()
    {
        return 'resource:taxonomy';
    }

    public function getLabel()
    {
        return 'Taxonomy'; // @translate
    }

    public function form(PhpRenderer $view)
    {
        return $view->partial('taxonomy/common/data-type/taxonomy', [
            'dataType' => $this->getName(),
            'resource' => $view->resource,
        ]);
    }

    public function valueAnnotationPrepareForm(PhpRenderer $view)
    {
    }

    public function valueAnnotationForm(PhpRenderer $view)
    {
        return $view->partial('common/data-type/value-annotation-resource', [
            'dataTypeLabel' => $view->translate('Taxonomies'),
            'dataTypeSingle' => 'taxonomy',
            'dataTypePlural' => 'taxonomies',
        ]);
    }

    public function getValidValueResources()
    {
        return [Entity\Taxonomy::class];
    }
}
