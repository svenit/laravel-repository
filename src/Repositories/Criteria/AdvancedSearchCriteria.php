<?php

namespace VyDev\Repositories\Criteria;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use VyDev\Repositories\Criteria\Criteria;
use VyDev\Repositories\Contracts\RepositoryInterface;

class AdvancedSearchCriteria extends Criteria
{
    private $request;
    private $config;

    public function __construct()
    {
        $this->request = app()->request;
        $this->config = config('repositories.parameters');
    }

    public function apply($model,RepositoryInterface $repository)
    {   
        $search = config('repositories.parameters.search');

        if($this->request->get($search))
        {
            $operators = ['=','!=','>','<','>=','<=','LIKE'];

            $searchAttributes = explode(';',$this->request->get($search));

            foreach($searchAttributes as $key => $attribute)
            {
                $mappingAttrs = explode(':',$attribute);
                [$field,$operator,$value] = $mappingAttrs;
                if(in_array($operator,$operators))
                {
                    $model = $model->where($field,$operator,strtolower($operator) == "like" ? "%{$value}%" : $value);
                }
                else
                {
                    throw new Exception("Operator is only support ['=','!=','>','<','>=','<=','LIKE']");
                }
            }
        }
        return $model;
    }

    public function filterAttributes($model,$findSearchCriteria)
    {
        $filterKeys = app()->request->{$this->config['filter']};

        if($findSearchCriteria->count() >= 1)
        {
            if($filterKeys)
            {
                $relationKey = [];
                $scopeKey = [];

                $filters = explode(';',$filterKeys);

                foreach($filters as $key => $filter)
                {
                    if(Str::contains($filter,'.'))
                    {
                        [$relation,$field] = explode('.',$filter);
                        $model = $filter == '*' ? $model->with($relation) : $model->with("$relation:$field");
                        array_push($relationKey,"{$relation}_id");
                    }
                    else
                    {
                        array_push($scopeKey,$filter);
                    }
                }
                $selected = array_merge($scopeKey,$relationKey);
                $model = $model->addSelect($selected);
            }
        }
        return $model;
    }
}
