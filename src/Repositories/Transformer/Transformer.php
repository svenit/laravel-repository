<?php

namespace VyDev\Repositories\Transform;

use VyDev\Repositories\Contracts\RepositoryInterface;

abstract class Transform
{
    public abstract function transform(RepositoryInterface $repository);
}