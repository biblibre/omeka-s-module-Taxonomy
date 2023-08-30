<?php

namespace Taxonomy\Entity;

use Omeka\Entity\Resource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    /**
     * @ManyToOne(targetEntity="TaxonomyTerm", inversedBy="children")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @OneToMany(targetEntity="TaxonomyTerm", mappedBy="parent")
     */
    protected Collection $children;

    public function __construct()
    {
        parent::__construct();

        $this->children = new ArrayCollection();
    }

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

    public function getParent(): ?TaxonomyTerm
    {
        return $this->parent;
    }

    public function setParent(?TaxonomyTerm $parent)
    {
        $this->parent = $parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(TaxonomyTerm $child)
    {
        $this->children->add($child);
    }
}
