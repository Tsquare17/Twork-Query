<?php

namespace Twork\Query;

/**
 * Class TaxQuery
 * @package Twork\Query
 */
class TaxQuery
{
    /**
     * @var string $taxonomy
     */
    protected $taxonomy;

    /**
     * @var string $field
     */
    protected $field = 'term_id';

    /**
     * @var array $terms
     */
    protected $terms = [];

    /**
     * @var string $relation
     */
    protected $relation = 'AND';

    /**
     * @var string $operator
     */
    protected $operator = 'IN';

    /**
     * @var bool $includeChildren
     */
    protected $includeChildren = true;

    /**
     * @return mixed
     */
    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    /**
     * @param mixed $taxonomy
     * @return TaxQuery
     */
    public function taxonomy($taxonomy): TaxQuery
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return TaxQuery
     */
    public function field(string $field): TaxQuery
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return array
     */
    public function getTerms(): array
    {
        return $this->terms;
    }

    /**
     * @param array $terms
     * @return TaxQuery
     */
    public function terms(array $terms): TaxQuery
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @param string $relation
     * @return TaxQuery
     */
    public function relation(string $relation): TaxQuery
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @return TaxQuery
     */
    public function operator(string $operator): TaxQuery
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludingChildren(): bool
    {
        return $this->includeChildren;
    }

    /**
     * @param bool $includeChildren
     * @return TaxQuery
     */
    public function includeChildren(bool $includeChildren): TaxQuery
    {
        $this->includeChildren = $includeChildren;

        return $this;
    }
}
