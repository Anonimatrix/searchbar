<?php

namespace Kompo\Searchbar;

use Kompo\Searchbar\Searchable\Searchable;
use Kompo\Searchbar\SearchItems\Stores\SearchStore;

class SearchService
{
    const DEFAULT_KEY = 'default';

    /**
     * @var \Kompo\Searchbar\Searchable\Searchable[]
     * @property \Kompo\Searchbar\Searchable\Searchable[] $searchables
     */
    protected $searchables = [];
    protected $key;
    protected $store;
    protected $storeKey;

    public function __construct($key = self::DEFAULT_KEY)
    {
        $this->key = $key;

        $this->setStoreKey();
    }

    // SETTINGS
    /**
     * @param \Kompo\Searchbar\Searchable\Searchable[] $searchables
     * @return void
     */
    public function setSearchables($searchables)
    {
        collect($searchables)->each(function($searchable, $key) {
            if (!in_array(Searchable::class, class_implements($searchable))) abort(500, __('crm.searchable-not-implemented', ['searchable' => $key]));
        });

        $this->searchables = $searchables;    
    }

    /**
     * Summary of getSearchables
     * @return \Illuminate\Support\Collection<\Kompo\Searchbar\Searchable\Searchable>
     */
    public function getSearchables()
    {
        return collect($this->searchables);
    }

    public function optionsSearchables()
    {
        $search = $this->getStore()->getState()->getSearch();
        
        return $this->getSearchables()->mapWithKeys(function($searchable) use($search) {
            $count = strlen($search) < 1 ? '?' : $this->getCountSpecificType($searchable);

            return [
                $searchable => _Link($searchable::searchableName() . " ($count)")
                                    ->selfPost('selectSearchableEntity', ['entity' => $searchable])
                                    ->refresh('navbar-search')->refresh()
            ];
        });
    }

    //QUERIES
    public function getQuery()
    {
        $state = $this->getStore()->getState();

        $rules = count($state->getRules()) == 0 && $state->getSearch() ? collect([
            // new ScopeRule('defaultSearch', $state->getSearch())
        ]) : $state->getRules();

        if(!$state->getSearchableEntity()) {
            return null;
        }

        return $rules->reduce(function($query, $rule) {
            return $rule->query($query);
        }, $state->getSearchableEntity()::baseSearchQuery())->with($state->getSearchableInstance()->getEagerRelationsKeys());
    }

    public function getCountSpecificType($type)
    {
        $model = (new $type)->injectContext($this);

        return $model->getInitialRule()->setSearchable($type)->query($model->baseSearchQuery())->count();
    }

    // STATES
    public function setStoreKey($key = null): SearchService
    {
        $this->storeKey = $key ?? ($this->key . time());

        return $this;
    }

    public function getStore(): SearchStore
    {
        if(!$this->store) {
            $this->store = app(SearchStore::class, ['key' => $this->storeKey, 'contextService' => $this]);
        }
        
        return $this->store;
    }

    public function getStoreKey(): ?string
    {
        return $this->storeKey;
    }
}