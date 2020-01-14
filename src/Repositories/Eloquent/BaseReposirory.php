<?php

namespace VyDev\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use VyDev\Repositories\Criteria\Criteria;
use VyDev\Repositories\Contracts\CriteriaInterface;
use VyDev\Repositories\Contracts\TransformInterface;
use VyDev\Repositories\Contracts\RepositoryInterface;
use VyDev\Repositories\Exceptions\RepositoryException;

abstract class BaseRepository implements RepositoryInterface,CriteriaInterface,TransformInterface 
{
    private $model;

    protected $app;
    protected $criteria;
    protected $transform;
    protected $skipCriteria;

    public function __construct(App $app,Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->initialize();
        $this->makeModel();
        $this->boot();
    }
    public abstract function model();
    public abstract function boot();
    public abstract function transform(RepositoryInterface $repository);

    public function initialize()
    {
        $this->skipCriteria = false;
    }
    
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        if(!$model instanceof Model)
        {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }
        return $this->model = $model;
    }

    public function applyCriteria()
    {
        if(!$this->skipCriteria)
        {
            foreach($this->criteria as $criteria)
            {
                if($criteria instanceof Criteria)
                {
                    $this->model = $criteria->apply($this->model,$this);
                }
            }
        }
        return $this;
    }

    public function skipCriteria()
    {
        $this->skipCriteria = true;
    }

    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model,$this);
        return $this;
    }

    public function pushCriteria(Criteria $criteria)
    {
        if($criteria instanceof Criteria)
        {
            $this->criteria->push($criteria);
        }
        return $this;
    }

    public function pushManyCriterias(...$criterias)
    {
        foreach($criterias as $criteria)
        {
            $this->pushCriteria($criteria);
        }
        return $this;
    }

    public function removeCriteria(Criteria $criteria)
    {
        $this->criteria = $this->criteria->reject(function($item) use ($criteria){
            return $item == $criteria;
        });
    }

    public function removeManyCriterias(...$criterias)
    {
        foreach($criterias as $criteria)
        {
            $this->removeCriteria($criteria);
        }
    }

    public function setTransform()
    {
        if($this->transform)
        {
            if(isset($this->model[0]))
            {
                $this->model->map(function($item,$key){
                    collect($this->transform($item))->map(function($trans,$index) use ($key){
                        unset($this->model[$key][$trans['field']]);
                        return $this->model[$key][$index] = $trans['value'];
                    });
                });
            }
            else
            {
                collect($this->transform($this->model))->map(function($trans,$index){
                    unset($this->model[$trans['field']]);
                    return $this->model[$index] = $trans['value'];
                });
            }
            return $this;
        }
    }

    public function all()
    {
        $this->applyCriteria();
        $this->model = $this->model->all();
        return $this;
    }

    public function get($columns = ['*'])
    {
        $this->applyCriteria();
        $this->model = $this->model->get(Arr::wrap($columns));
        return $this;
    }
    public function first()
    {
        $this->applyCriteria();
        $this->model = $this->model->first();
        return $this;
    }

    public function find($id)
    {
        $this->applyCriteria();
        $this->model = $this->model->find($id);
        return $this;
    }

    public function pluck($column, $key = null)
    {
        $this->applyCriteria();
        $this->model = $this->model->pluck($column, $key);
        return $this;
    }

    public function sync($attributes)
    {
        return $this->model->sync(Arr::wrap($attributes));
    }

    public function syncWithoutDetaching($attributes)
    {
        return $this->model->syncWithoutDetaching(Arr::wrap($attributes));
    }

    public function attach($attributes)
    {
        return $this->model->attach(Arr::wrap($attributes));
    }

    public function detach($attributes)
    {
        return $this->model->detach(Arr::wrap($attributes));
    }

    public function count()
    {
        $this->applyCriteria();
        return $this->model->count();
    }

    public function firstOrNew(array $attributes)
    {
        $this->applyCriteria();
        $this->model = $this->model->firstOrNew($attributes);
        return $this;
    }

    public function firstOrCreate(array $attributes)
    {
        $this->applyCriteria();
        $this->model = $this->model->firstOrCreate($attributes);
        return $this;
    }

    public function limit($limit)
    {
        $this->applyCriteria();
        $this->model = $this->model->limit($limit);
        return $this;
    }

    public function take($limit)
    {
        $this->applyCriteria();
        $this->model = $this->model->take($limit);
        return $this;
    }

    public function paginate($limit = null, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->model = $this->model->paginate($limit, $columns);
        return $this;
    }

    public function where($field, $value = null)
    {
        $this->applyCriteria();
        $this->model = $this->model->where($field, $value);
        return $this;
    }
    
    public function whereIn($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereIn($field,Arr::wrap($values));
        return $this;
    }

    public function whereNotIn($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereNotIn($field,Arr::wrap($values));
        return $this;
    }

    public function whereBetween($field,$values)
    {
        $this->applyCriteria();
        $this->model = $this->model->whereBetween($field,Arr::wrap($values));
        return $this;
    }

    public function create($values)
    {
        return $this->model->create($values);
    }

    public function update($values)
    {
        return $this->model->update($values);
    }

    public function delete()
    {
        return $this->model->delete();
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function has($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->has($relation);
        return $this;
    }

    public function with($relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->with($relations);
        return $this;
    }

    public function withCount($relations)
    {
        $this->applyCriteria();
        $this->model = $this->model->withCount($relations);
        return $this;
    }

    public function whereHas($relation, $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);
        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);
        return $this;
    }

    public function load($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->load($relation);
        return $this;
    }
    
    public function relation($relation)
    {
        $this->applyCriteria();
        $this->model = $this->model->{$relation}()->get();
        return $this;
    }

    public function search($fields,$value)
    {
        foreach(Arr::wrap($fields) as $builder)
        {
            $this->model = $this->model->orWhere($builder,'LIKE',"%$value%");
        }
        return $this;
    }
    public function hidden($columns = ['*'])
    {
        $this->model = $this->model->setHidden(Arr::wrap($columns));
        return $this;
    }

    public function visible($columns = ['*'])
    {
        $this->model = $this->model->setVisible(Arr::wrap($columns));
        return $this;
    }

    public function export()
    {
        $this->setTransform();
        return $this->model;
    }

    public function resetModel()
    {
        $this->makeModel();
    }
    

}
