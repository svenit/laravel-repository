<?php

namespace VyDev\Repositories\Events;

use VyDev\Repositories\Events\BaseEvent;


class EntityUpdated extends BaseEvent
{
    protected $action = 'updated';
}