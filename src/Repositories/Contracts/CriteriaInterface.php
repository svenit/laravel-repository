<?php

namespace VyDev\Repositories\Contracts;

use VyDev\Repositories\Criteria\Criteria;

interface CriteriaInterface
{
    public function applyCriteria();
    public function pushCriteria(Criteria $criteria);
    public function pushCriteriaWhen(array $arguments);
    public function pushManyCriterias(Criteria ...$criterias);
    public function skipCriteria();
    public function getByCriteria(Criteria $criteria);
    public function popCriteria(Criteria $criteria);
    public function popCriteriaWhen(array $arguments);
    public function popManyCriterias(Criteria ...$criterias);
}