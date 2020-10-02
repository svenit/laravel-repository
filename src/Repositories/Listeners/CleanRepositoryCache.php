<?php

namespace VyDev\Repositories\Listeners;

use VyDev\Repositories\Events\BaseEvent;


class CleanRepositoryCache
{
    protected $cache;
    protected $model;
    protected $repository;
    protected $action;

    public function handle(BaseEvent $event)
    {
        $this->info($event->getModel());
    }
}