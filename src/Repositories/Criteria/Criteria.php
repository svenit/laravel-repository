<?php

namespace VyDev\Repositories\Criteria;

use  VyDev\Repositories\Contracts\RepositoryInterface;

abstract class Criteria
{
    public abstract function apply($model, RepositoryInterface $repository);
}