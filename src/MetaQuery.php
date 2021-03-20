<?php

namespace Twork\Query;

/**
 * Class MetaQuery
 * @package Twork\Query
 */
class MetaQuery
{
    /**
     * @var string $key
     */
    protected $key;

    /**
     * @var string $value
     */
    protected $value;

    /**
     * @var string $compare
     */
    protected $compare = '=';

    /**
     * @var string $relation
     */
    protected $relation = 'AND';

    /**
     * @var string $type
     */
    protected $type = 'CHAR';

    /**
     * @param string $key
     * @return MetaQuery
     */
    public function key(string $key): MetaQuery
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param string $value
     * @return MetaQuery
     */
    public function value(string $value): MetaQuery
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param string $compare
     * @return MetaQuery
     */
    public function compare(string $compare): MetaQuery
    {
        $this->compare = $compare;

        return $this;
    }

    /**
     * @param string $relation
     * @return MetaQuery
     */
    public function relation(string $relation): MetaQuery
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @param string $type
     * @return MetaQuery
     */
    public function type(string $type): MetaQuery
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCompare()
    {
        return $this->compare;
    }

    /**
     * @return string
     */
    public function getRelation(): string
    {
        return $this->relation;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
