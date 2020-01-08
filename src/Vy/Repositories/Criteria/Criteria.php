<?php

namespace VyDev\EasyRepository\Vy\Repositories\Criteria;

use  VyDev\EasyRepository\Vy\Repositories\Contracts\RepositoryInterface;

abstract class Criteria
{
    public abstract function apply($model,RepositoryInterface $repository);
}