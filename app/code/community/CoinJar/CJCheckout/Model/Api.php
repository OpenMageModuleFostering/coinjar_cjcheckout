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

class CoinJar_CJCheckout_Model_Api
{
    protected $_paymentMethodCode   = null;
    protected $_config              = null;
    protected $_quote               = null;
    protected $_quoteId             = null;

    public function __construct($params)
    {
        if ($params['payment_method_code']) {
            $this->_paymentMethodCode = $params['payment_method_code'];
        }
    }

    /**
     * Builds a HTTP Client for coinjar order API
     *
     * @return  Varien_Http_Client
     */
    public function buildClient()
    {
        $clientUrl = $this->_getConfig()->getApiUrl();

        $client = new Varien_Http_Client($clientUrl);
        $client->setAuth($this->_getConfig()->getMerchantUuid(), $this->_getConfig()->getMerchantSecret());
        $client->setMethod(Varien_Http_Client::POST);

        return $client;
    }

    /**
     * Builds POST parameters for use in a Varien_Http_Client request
     *
     * @return  array
     */
    public function buildRequestParams($orderId = null, $reference = null)
    {
        $orderId          = is_null($orderId) ? $this->_getCheckoutSession()->getLastRealOrderId() : $orderId;
        $order            = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $grandTotal       = $order->getGrandTotal();
        $subTotal         = $order->getSubtotal();
        $shippingHandling = $order->getShippingAmount();

        $postParams = array();
        $postParams['currency']           = Mage::app()->getStore()->getCurrentCurrencyCode();
        $postParams['merchant_invoice']   = $orderId;
        $postParams['merchant_reference'] = is_null($reference) ? $orderId : $reference;
        $postParams['return_url']         = Mage::getUrl('cjcheckout/standard/success');
        $postParams['cancel_url']         = Mage::getUrl('cjcheckout/standard/cancel');
        $postParams['notify_url']         = Mage::getUrl('cjcheckout/standard/notify');
        $postParams['order_items_attributes'] = array();

        foreach ($order->getItemsCollection()->getItems() as $item) {
            $sku       = $item->getSku();
            $unitPrice = $item->getPrice();
            $qty       = $item->getQtyOrdered();
            $desc      = $item->getName();
            array_push($postParams['order_items_attributes'], array(
                'name'      => $desc,
                'quantity'  => $qty,
                'amount'    => $unitPrice
            ));
        }
        array_push($postParams['order_items_attributes'], array(
            'name'      => 'Shipping and handling',
            'quantity'  => 1,
            'amount'    => $shippingHandling
        ));

        Mage::helper('cjcheckout')->log('POST: ' . print_r($postParams, true));

        return $postParams;
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_getCheckoutSession()->setQuoteId($this->_getQuoteId());
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Return quote ID
     *
     * @return Mage_Sale_Model_Quote
     */
    protected function _getQuoteId()
    {
        if (!$this->_quoteId) {
            $this->_quoteId = $this->_getCheckoutSession()->getData('coinjar_quote_id', true);
        }
        return $this->_quoteId;
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