<?php
namespace Disturb\Services;

interface StepServiceInterface
{
    public function execute(array $paramHash) : bool;
}

