<?php

namespace VyDev\EasyRepository\Vy\Repositories\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use VyDev\EasyRepository\Vy\Repositories\Criteria\Criteria;
use VyDev\EasyRepository\Vy\Repositories\Contracts\CriteriaInterface;
use VyDev\EasyRepository\Vy\Repositories\Contracts\TransformInterface;
use VyDev\EasyRepository\Vy\Repositories\Contracts\RepositoryInterface;
use VyDev\EasyRepository\Vy\Repositories\Exceptions\RepositoryException;

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
        $this->transform = false;
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
        if($this->transform)
        {
            $this->setTransform();
        }
        return $this->model;
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
    /**
     * Set transformer status equal true
     * @param null
     * @return void
     * 
    */
    public function useTransformer()
    {
        $this->transform = true;
    }

    public function setTransform()
    {
        $this->model->map(function($item,$key){
            $this->model[$key] = $this->transform($item);
        });
        return $this;
    }
}