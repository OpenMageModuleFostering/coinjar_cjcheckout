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

class CoinJar_CJCheckout_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    protected $_config              = null;
    protected $_quote               = null;
    protected $_quoteId             = null;
    protected $_order               = null;
    protected $_checkout            = null;
    protected $_api                 = null;
    protected $_ipn                 = null;

    /**
     * Instantiate checkout object
     *
     * @throws Mage_Core_Exception
     */
    protected function _getOnepage()
    {
        if (is_null($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/type_onepage');
            $this->_checkout->initCheckout();
        }
        return $this->_checkout;
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    protected function _getQuote()
    {
        if (is_null($this->_quote)) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Protected getter for API model object
     *
     * @return  CoinJar_CJCheckout_Model_Api
     */
    protected function _getApi()
    {
        if (is_null($this->_api)) {
            $this->_api = Mage::getModel('cjcheckout/api', array('payment_method_code' => $this->_paymentMethodCode));
        }
        return $this->_api;
    }

    /**
     * Protected getter for IPN model object
     *
     * @return  CoinJar_CJCheckout_Model_Ipn
     */
    protected function _getIpn()
    {
        if (is_null($this->_ipn)) {
            $this->_ipn = Mage::getModel('cjcheckout/ipn');
        }
        return $this->_ipn;
    }

    /**
     * Protected getter for config model object
     *
     * @return  CoinJar_CJCheckout_Model_Config
     */
    protected function _getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = Mage::getModel('cjcheckout/config', array($this->_paymentMethodCode, Mage::app()->getStore()->getId()));
        }
        return $this->_config;
    }

}