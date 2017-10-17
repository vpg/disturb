<?php
namespace Vpg\Disturb\Services;

interface StepServiceInterface
{
    public function execute(array $paramHash) : array;
}

