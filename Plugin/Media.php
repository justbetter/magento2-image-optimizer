<?php

namespace JustBetter\ImageOptimizer\Plugin;

use JustBetter\ImageOptimizer\Helper\Data;
use Magento\MediaStorage\Helper\File\Media as MagentoStorageMedia;
use Psr\Log\LoggerInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class Media
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $imageOptimizerHelper;

    /**
     * @var array
     */
    protected $config;

    /**
     * Media constructor.
     *
     * @param LoggerInterface $logger
     * @param Data            $imageOptimizerHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Data $imageOptimizerHelper
    )
    {
        $this->imageOptimizerHelper = $imageOptimizerHelper;
        $this->config = $imageOptimizerHelper->collectModuleConfig();
        $this->logger= $logger;
    }

    /**
     * @param MagentoStorageMedia                     $media
     * @param                                         $mediaDirectory
     * @param                                         $path
     */
    public function beforeCollectFileInfo(MagentoStorageMedia $media, $mediaDirectory, $path)
    {
        if ($this->imageOptimizerHelper->isActive()) {
            $path = ltrim($path, '\\/');
            $fullPath = $mediaDirectory . '/' . $path;

            $optimizerChain = OptimizerChainFactory::create();

            if (array_key_exists('log', $this->config) && $this->config['log']) {
                $optimizerChain = $optimizerChain->useLogger($this->logger);
            }

            $optimizerChain->optimize($fullPath);
        }
    }
}
