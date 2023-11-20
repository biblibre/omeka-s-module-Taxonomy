<?php

namespace Taxonomy\Site\BlockLayout;

use Laminas\Form\Form;
use Laminas\Form\FormElementManager;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Taxonomy\Form\Element\TaxonomySelect;

class TaxonomyTermTree extends AbstractBlockLayout
{
    protected $formElementManager;

    public function __construct(FormElementManager $formElementManager)
    {
        $this->formElementManager = $formElementManager;
    }

    public function getLabel()
    {
        return 'Taxonomy term tree'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site, SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null)
    {
        $taxonomySelect = $this->formElementManager->get(TaxonomySelect::class);
        $taxonomySelect->setName('o:block[__blockIndex__][o:data][taxonomy_id]');
        $taxonomySelect->setLabel('Taxonomy'); // @translate
        $taxonomySelect->setOption('disable_group_by_owner', true);
        if ($block) {
            $taxonomySelect->setValue($block->dataValue('taxonomy_id'));
        }

        return $view->formRow($taxonomySelect);
    }

    public function prepareRender(PhpRenderer $view)
    {
        $view->headLink()->appendStylesheet($view->assetUrl('css/taxonomy-term-tree.css', 'Taxonomy'));
        $view->headScript()->appendFile($view->assetUrl('vendor/jstree/jstree.min.js', 'Omeka'));
        $view->headScript()->appendFile($view->assetUrl('js/taxonomy-term-tree.js', 'Taxonomy'));
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        $taxonomy_id = $block->dataValue('taxonomy_id');
        try {
            $taxonomy = $view->api()->read('taxonomies', $taxonomy_id)->getContent();
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
            $view->logger()->err(sprintf('Taxonomy not found: %d', $taxonomy_id));

            return '';
        }

        $values = [
            'block' => $block,
            'taxonomy' => $taxonomy,
        ];
        return $view->partial('taxonomy/common/block-layout/taxonomy-term-tree', $values);
    }
}
