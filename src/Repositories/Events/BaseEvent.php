<?php

namespace VyDev\Repositories\Events;

use Illuminate\Database\Eloquent\Model;
use VyDev\Repositories\Contracts\RepositoryInterface;


abstract class BaseEvent
{

    protected $model;
    protected $repository;
    protected $action;

    public function __construct(RepositoryInterface $repository, Model $model)
    {
        $this->repository = $repository;
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getAction()
    {
        return $this->action;
    }

}