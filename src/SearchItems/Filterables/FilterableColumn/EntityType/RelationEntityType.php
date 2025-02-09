<?php

namespace Kompo\Searchbar\SearchItems\Filterables\FilterableColumn\EntityType;

class RelationEntityType extends EntityType
{
    protected $relation;
    protected $baseQuery;

    public function __construct($relation, string $baseQuery)
    {
        if (!is_subclass_of($relation, 'Kompo\Searchbar\RelationSearchable')) {
            throw new \Exception('Relation must implement RelationSearchable interface');
        }

        $this->relation = $relation;
        $this->baseQuery = $baseQuery;
    }

    public function optionsWithLabels()
    {
        $baseQuery = $this->baseQuery;

        return $this->relation::parseOptions($this->relation::$baseQuery()->get());
    }

    public function getValue()
    {
        return $this->relation;
    }

    public function from($value)
    {
        return $this->relation::find($value);
    }
}