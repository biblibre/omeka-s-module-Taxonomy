<?php

namespace Taxonomy\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Omeka\Entity\Resource;

/**
 * @Entity
 */
class Taxonomy extends Resource
{
    /**
     * @Column(type="string", unique=true)
     */
    protected string $code;

    /**
     * @OneToMany(targetEntity="TaxonomyTerm", mappedBy="taxonomy", indexBy="code")
     */
    protected Collection $terms;

    public function __construct()
    {
        parent::__construct();

        $this->terms = new ArrayCollection();
    }

    public function getResourceName()
    {
        return 'taxonomies';
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function getTerms(): array
    {
        return $this->terms->toArray();
    }

    public function getTerm(string $code)
    {
        if (!isset($this->terms[$code])) {
            return null;
        }

        return $this->terms[$code];
    }

    public function addTerm(TaxonomyTerm $term)
    {
        $this->terms[$term->getCode()] = $term;
    }
}
