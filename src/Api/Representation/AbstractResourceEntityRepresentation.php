<?php

namespace Taxonomy\Api\Representation;

abstract class AbstractResourceEntityRepresentation extends \Omeka\Api\Representation\AbstractResourceEntityRepresentation
{
    public function subjectValueTotalCountCompat($propertyId = null, $resourceType = null, $siteId = null)
    {
        if (version_compare(\Omeka\Module::VERSION, '4.0', '<')) {
            // Method subjectValueTotalCount has only one parameter before 4.0
            return $this->subjectValueTotalCount($propertyId);
        }
        if (version_compare(\Omeka\Module::VERSION, '4.1', '<')) {
            // Method subjectValueTotalCount has been removed in 4.0
            return $this->getAdapter()->getSubjectValueTotalCount($this->resource, $propertyId);
        }

        return $this->subjectValueTotalCount($propertyId, $resourceType, $siteId);
    }

    public function displaySubjectValuesCompat(array $options = [])
    {
        if (version_compare(\Omeka\Module::VERSION, '4.0', '<')) {
            $page = $options['page'] ?? null;
            $perPage = $options['perPage'] ?? null;
            $resourceProperty = $options['resourceProperty'] ?? null;

            return $this->displaySubjectValues($page, $perPage, $resourceProperty);
        }

        return $this->displaySubjectValues($options);
    }
}
