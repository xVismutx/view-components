<?php
namespace Nayjest\ViewComponents\Data\Processors;

use Nayjest\ViewComponents\Data\Operations\OperationInterface;

interface ProcessorInterface
{
    /**
     * Applies operation to source and returns modified source.
     *
     * @param $src
     * @param OperationInterface $operation
     * @return mixed
     */
    public function process($src, OperationInterface $operation);
}
