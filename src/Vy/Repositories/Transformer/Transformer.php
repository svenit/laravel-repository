<?php

namespace VyDev\EasyRepository\Vy\Repositories\Transform;

use VyDev\EasyRepository\Vy\Repositories\Contracts\RepositoryInterface;

abstract class Transform
{
    public abstract function transform(RepositoryInterface $repository);
}