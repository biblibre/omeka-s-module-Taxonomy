<?php

namespace Taxonomy\Entity;

use Omeka\Entity\Resource;

/**
 * @Entity
 * @Table(uniqueConstraints={@UniqueConstraint(name="UNIQ_TAXONOMY_ID_CODE", columns={"taxonomy_id", "code"})})
 */
class TaxonomyTerm extends Resource
{
    /**
     * @Column(type="string")
     */
    protected $code;

    /**
     * @ManyToOne(targetEntity="Taxonomy", inversedBy="terms")
     * @JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $taxonomy;

    public function getResourceName()
    {
        return 'taxonomy_terms';
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    public function setTaxonomy(Taxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }
}
