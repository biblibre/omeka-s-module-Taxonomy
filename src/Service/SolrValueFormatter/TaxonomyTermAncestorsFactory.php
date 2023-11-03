<?php
namespace Taxonomy\Service\SolrValueFormatter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Taxonomy\SolrValueFormatter\TaxonomyTermAncestors;

class TaxonomyTermAncestorsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $apiManager = $services->get('Omeka\ApiManager');
        $valueFormatter = new TaxonomyTermAncestors($apiManager);

        return $valueFormatter;
    }
}
