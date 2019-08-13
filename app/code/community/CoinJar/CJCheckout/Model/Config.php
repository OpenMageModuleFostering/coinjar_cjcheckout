<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    CoinJar
 * @package     CoinJar_CJCheckout
 * @copyright   Copyright (c) 2014 CoinJar (http://coinjar.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CoinJar_CJCheckout_Model_Config
{
    /**
     * CoinJar Standard
     *
     * @var string
     */
    const METHOD_CJ_STANDARD = 'cjcheckout_standard';

    /**
     * Refund types
     *
     * @var string
     */
    const REFUND_TYPE_FULL = 'Full';
    const REFUND_TYPE_PARTIAL = 'Partial';

    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * Current store id
     *
     * @var bool
     */
    protected $_sandboxFlag = null;

    /**
     * Current method code
     *
     * @var bool
     */
    protected $_methodCode = null;

    /**
     * Currency codes supported by CoinJar Order API
     *
     * @var array
     */
    protected $_supportedCurrencyCodes = array('BTC', 'USD', 'AUD', 'NZD', 'CAD', 'EUR', 'GBP', 'SGD', 'HKD', 'CHF', 'JPY');

    /**
     * Set method and store id, if specified
     *
     * @param array $params
     */
    public function __construct($params = array())
    {
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            if ($params) {
                $storeId = array_shift($params);
                $this->setStoreId($storeId);
            }
        }
        $this->setSandboxFlag($this->getConfigData('sandbox'));
    }

    /**
     * Method code setter
     *
     * @param string|Mage_Payment_Model_Method_Abstract $method
     * @return CoinJar_CJCheckout_Model_Config
     */
    public function setMethod($method)
    {
        if ($method instanceof Mage_Payment_Model_Method_Abstract) {
            $this->_methodCode = $method->getCode();
        } elseif (is_string($method)) {
            $this->_methodCode = $method;
        }
        return $this;
    }

    /**
     * Store ID setter
     *
     * @param int $storeId
     * @return CoinJar_CJCheckout_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Sandbox Flag setter
     *
     * @param int $sandboxFlag
     * @return CoinJar_CJCheckout_Model_Config
     */
    public function setSandboxFlag($sandboxFlag)
    {
        $this->_sandboxFlag = (bool)$sandboxFlag;
        return $this;
    }

    /**
     * CoinJar API URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getApiUrl(array $params = array())
    {
        return sprintf('https://checkout.%scoinjar.io/api/v1/orders.json',
            $this->_sandboxFlag ? 'sandbox.' : '',
            $params ? '?' . http_build_query($params) : ''
        );
    }

    /**
     * CoinJar API URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getCoinjarOrderUrl(array $params = array())
    {
        return sprintf('https://checkout.%scoinjar.io/orders',
            $this->_sandboxFlag ? 'sandbox.' : '',
            $params ? '?' . http_build_query($params) : ''
        );
    }

    /**
     * CoinJar Merchant UUID getter
     *
     * @param array $params
     * @return string
     */
    public function getMerchantUuid()
    {
        return Mage::helper('core')->decrypt($this->getConfigData('merchantuuid'));
    }

    /**
     * CoinJar Merchant Secret getter
     *
     * @param array $params
     * @return string
     */
    public function getMerchantSecret()
    {
        return Mage::helper('core')->decrypt($this->getConfigData('merchantsecret'));
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param string $code
     * @return bool
     */
    public function isCurrencyCodeSupported($code)
    {
        if (in_array($code, $this->_supportedCurrencyCodes)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @return mixed
     */
    public function getConfigData($field)
    {
        $path = 'payment/'.$this->_methodCode.'/'.$field;
        return Mage::getStoreConfig($path, $this->_storeId);
    }

}

