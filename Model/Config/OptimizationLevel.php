<?php

namespace JustBetter\ImageOptimizer\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class OptimizationLevel implements ArrayInterface
{
    public function toOptionArray()
    {
        $options = [];

        for ($i=2; $i <8; $i++) {
            $options[] = $i;
        }

        return $options;
    }
}
