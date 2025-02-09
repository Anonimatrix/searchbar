<?php

namespace Kompo\Searchbar\Searchable;

use Kompo\Searchbar\InjectableContextTrait;
use Kompo\Searchbar\SearchItems\Filterables\Filterable;

trait SearchableModelUtils
{
	use InjectableContextTrait;

	public static function getBaseFilterable()
	{
		if (defined(self::class . '::BASE_FILTERABLE')) {
			return self::BASE_FILTERABLE;
		}

		return 'name_filter';
	}

	public function getTableClass()
	{
		return config('searchbar.base_result_table_namespace') . '\\Search' . class_basename(self::class) . 'Table';
	}

	public final function getTableClassInstance($parameters = [])
	{
		return new ($this->getTableClass())($parameters);
	}

	public static function searchableName()
	{
		return __('filter.' . strtolower(class_basename(self::class)));
	}

    public function filterable($key): Filterable
	{
		return $this->decoratedFilterables()[$key];
	}

	public function decoratedFilterables()
	{
		return collect(self::filterables())->map(function ($filterable) {
			return $filterable->injectContext($this->searchContextService);
		});
	}


	public function decoratedSections()
	{
		return collect(self::sections())->map(function ($section) {
			return $section->injectContext($this->searchContextService);
		});
	}

	public function getInitialRule()
	{
		$state = searchService()->getStore()->getState();

		if(!$state->getSearch()) {
			return null;
		}

		/**
		 * @var \App\Searchbar\Filterables\FilterableColumn\FilterableColumn $filterable
		 */
		$filterable = $this->filterable(self::getBaseFilterable());

		if (!($filterable instanceof \App\Searchbar\Filterables\FilterableColumn\FilterableColumn)) {
			throw new \Exception('The base filterable must be a FilterableColumn');
		}

		return ($filterable->getRuleInstance([
			'value' => $state->getSearch(),
		]))->setKeyReference(self::getBaseFilterable())->injectContext($this->searchContextService);
	}

	public static function baseSearchQuery()
	{
		return self::query();
	}

	public function getEagerRelationsKeys()
	{
		return [];
	}
}