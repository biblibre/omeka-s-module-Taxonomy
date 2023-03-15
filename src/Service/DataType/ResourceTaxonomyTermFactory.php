<?php
namespace Taxonomy\Service\DataType;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Taxonomy\DataType\Resource\TaxonomyTerm;

class ResourceTaxonomyTermFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $matches = [];
        if (!preg_match('/^resource:taxonomy-term:(.+)$/', $requestedName, $matches)) {
            throw new \Exception("Invalid requested name: " . $requestedName);
        }

        $taxonomyCode = $matches[1];
        $api = $services->get('Omeka\ApiManager');
        $taxonomies = $api->search('taxonomies', ['code' => $taxonomyCode, 'limit' => 1])->getContent();
        if (empty($taxonomies)) {
            throw new \Exception('Invalid taxonomy code: ' . $taxonomyCode);
        }

        $taxonomy = reset($taxonomies);

        $dataType = new TaxonomyTerm($taxonomy);

        return $dataType;
    }
}
