<?php

namespace VyDev\EasyRepository\Vy\Repositories\Contracts;

use VyDev\EasyRepository\Vy\Repositories\Criteria\Criteria;

interface CriteriaInterface
{
    public function applyCriteria();
    public function pushCriteria(Criteria $criteria);
    public function skipCriteria();
    public function getByCriteria(Criteria $criteria);
    public function pushManyCriterias(...$criterias);
    public function removeCriteria(Criteria $criteria);
    public function removeManyCriterias(...$criterias);
}