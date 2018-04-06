<?php

namespace JustBetter\ImageOptimizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package JustBetter\Sentry\Helper
 */
class Data extends AbstractHelper
{

    const XML_PATH_SRS = 'image_optimizer/general/';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $configKeys = [
        'log',
        'enabled'
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
    )
    {
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
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @param      $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SRS . $code, $storeId);
    }

    /**
     * @return array
     */
    public function collectModuleConfig()
    {
        foreach ($this->configKeys as $key => $value) {
            $this->config[ $value ] = $this->getGeneralConfig($value);
        }

        return $this->config;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ( ! empty($this->config) && array_key_exists('enabled', $this->config) && $this->config['enabled']);
    }
}
