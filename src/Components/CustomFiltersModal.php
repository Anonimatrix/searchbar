<?php

namespace Kompo\Searchbar\Components;

use Kompo\Modal;

class CustomFiltersModal extends Modal
{
    use SearchStateRequestUtils;
    use SearchKomponentUtils;

    public $id = 'custom-filters-modal';
    protected $_Title = 'filter.filters';
    public $class = 'max-w-2xl w-screen';

    public function headerButtons()
	{
        return _FlexEnd(
            _ButtonOutlined('filter.reset-filter')->selfPost('getBack')->refresh()->refresh('navbar-search'),
            _Button('filter.new-rule')->selfGet('addRuleModal')->inModal(),
        )->class('gap-4');
	}

    public function body()
    {
        $typeInstance = $this->state->getSearchableInstance();

        return _Rows(
            _Rows(
                _Rows(
                    $this->rowRule(
                        fn($deleteButton) => $deleteButton->selfPost('getBack')->refresh()->refresh('navbar-search'),
                        _Html('filter.search-in')->col('!pr-0 col-md-3'),
                        _Html()->col('col-md-3'),
                        _Select()->name('searchableEntity')->options(searchService()->optionsSearchables())
                            ->default($this->state->getSearchableEntity())
                            ->selfPost('selectSearchableEntity')->refresh('navbar-search')
                            ->class('!mb-0 w-full')
                            ->col('col-md-6'),
                    ),
                )->class('mb-4'),
                _Rows($this->state->getFilterableRules()->map(function($r, $i) use ($typeInstance) {
                    $colInfo = $r->getFilterable($typeInstance);

                    return $this->rowRule(function($deleteButton) use ($i) {
                        return $deleteButton->selfPost('deleteRule', ['i' => $i])->refresh()->refresh('navbar-search');
                    }, ...$colInfo->formRow($r, $i));
                }))->class('gap-y-4'),
            ),
        );
    }

    protected function rowRule($deleteButtonCallback, ...$inputs)
    {
        return _Flex(
            _Columns(
                ...$inputs,
            )->class('items-center w-full'),

            $deleteButtonCallback(_DeleteLink()->icon(_Sax('trash', 22))->col('col-md-1')->class("text-gray-700 hover:text-danger"))
        )->class('gap-3');
    }

    public function footer()
    {
        return null;
    }

    public function executeCustomFilterableFunction($i, $function)
    {
        $rule = $this->state->getRules()->get($i);

        return $rule->getFilterable($this->state->getSearchableInstance())->executeCustomMethod($function, $i);
    }

    public function addRuleModal()
    {
        $searchableI = $this->state->getSearchableInstance();

        $cols = collect($searchableI::filterables());

        return _Rows(
            _Html('translate.add-rule')->class('text-2xl font-semibold mb-4'),
            _Rows($cols->map(function($col, $key) {
                return _Link($col->getName())->selfGet('getRuleForm', ['i' => $key])->inPanel('rule-details-form');
            }))->class('mb-4 gap-2'),

            _Panel()->id('rule-details-form'),
        )->class('p-4');
    }

    public function getRuleForm($key)
    {
        return $this->searchableInstance->filterable($key)->form($key, searchService()->getStoreKey());
    }
}