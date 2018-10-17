<?php

namespace JustBetter\ImageOptimizer\Helper;

use Magento\Store\Model\ScopeInterface;
use Spatie\ImageOptimizer\OptimizerChain;
use Magento\Framework\App\Helper\Context;
use Spatie\ImageOptimizer\Optimizers\Svgo;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Magento\Store\Model\StoreManagerInterface;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package JustBetter\Sentry\Helper
 */
class Data extends AbstractHelper
{
    protected $configPaths = [
        'image_optimizer/general/',
        'image_optimizer/jpg/',
        'image_optimizer/png/',
    ];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $configKeys = [
        'log',
        'enabled',
        'jpg_compression',
        'jpg_strip_all',
        'jpg_all_progressive',
        'png_quality_min_max',
        'png_interlace',
        'png_optimization_level',
    ];

    public $cliConfigKeys = [
        'jpg_compression' => '85',
        'jpg_strip_all' => true,
        'jpg_all_progressive' => true,
        'png_quality_min_max' => '65-80',
        'png_interlace' => true,
        'png_optimization_level' => 2,
    ];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var State
     */
    protected $appState;

    /**
     * Data constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;

        parent::__construct($context);
    }

    /**
     * @param      $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $path, $storeId = null)
    {
        return $this->getConfigValue($path . $code, $storeId);
    }

    /**
     * @return array
     */
    public function collectModuleConfig()
    {
        foreach ($this->configPaths as $path) {
            foreach ($this->configKeys as $configKey) {
                $configValue = $this->getGeneralConfig($configKey, $path);
                if ($configValue !== null) {
                    $this->config[ $configKey ] = $configValue;
                }
            }
        }

        return $this->config;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (! empty($this->config) && array_key_exists('enabled', $this->config) && $this->config['enabled']);
    }

    /**
     * Create custom image optimizerChain
     * to set custom options
     *
     * @return Spatie\ImageOptimizer\OptimizerChain
     */
    public function customOptimizerChain($options = [])
    {
        // if options are not empty overwrite
        // with config values
        if (! empty($options)) {
            foreach($options as $name => $value) {
                if ($value) {
                    $this->config[$name] = $value;
                }
            }
        }

        return (new OptimizerChain)
            ->addOptimizer(new Jpegoptim([
                '-m'.$this->config['jpg_compression'],
                (bool)$this->config['jpg_strip_all'] ? '--strip-all' : '',
                (bool)$this->config['jpg_all_progressive'] ? '--all-progressive' : '',
            ]))

            ->addOptimizer(new Pngquant([
                $this->config['png_quality_min_max'] ? '--quality '.$this->config['png_quality_min_max'] : '',
                '--force',
            ]))

            ->addOptimizer(new Optipng([
                '-i'.(int)$this->config['png_interlace'],
                '-o'.(int)$this->config['png_optimization_level'],
                '-quiet',
            ]))

            ->addOptimizer(new Svgo([
                '--disable=cleanupIDs',
            ]))

            ->addOptimizer(new Gifsicle([
                '-b',
                '-O3',
            ]));
    }
}
