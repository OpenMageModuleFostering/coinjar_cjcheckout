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

class CoinJar_CJCheckout_Model_Ipn
{
    protected $_ipnData             = array();
    protected $_paymentMethodCode   = null;
    protected $_config              = null;

    public function __construct($params)
    {
        if ($params['payment_method_code']) {
            $this->_paymentMethodCode = $params['payment_method_code'];
        }
    }

    /**
     * Setter for IPN data
     *
     * @param $data
     * @return CoinJar_CJCheckout_Model_Ipn
     */
    public function setIpnData(Array $data)
    {
        $this->_ipnData = $data;
        return $this;
    }

    /**
     * Getter for IPN data
     *
     * @param $key
     * @return mixed
     */
    public function getIpnData($key)
    {
        return $key ? $this->_ipnData[$key] : $this->_ipnData;
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    public function verify()
    {
        if (!$this->_validateIpnData()) {
            return false;
        }

        if (Mage::helper('cjcheckout/checkout')->debugIpn()) {
            return true;
        }

        $merchantSecret = $this->_getConfig()->getMerchantSecret();
        $combinedPost = $this->getIpnData('uuid')
                        . $this->getIpnData('amount')
                        . $this->getIpnData('currency')
                        . $this->getIpnData('status');

        $hmac = hash_hmac( 
            'sha256', 
            $combinedPost,
            $merchantSecret,
            FALSE
        );

        return $hmac == $this->getIpnData('ipn_digest');
    }

    /**
     * Validate that all of the necessary stored IPN data is present.
     *
     * @return bool
     */
    protected function _validateIpnData()
    {
        return !(
            is_null($this->getIpnData('ipn_digest'))
            || is_null($this->getIpnData('uuid'))
            || is_null($this->getIpnData('amount'))
            || is_null($this->getIpnData('currency'))
            || is_null($this->getIpnData('status'))
            || is_null($this->getIpnData('merchant_invoice'))
        );
    }

    /**
     * Process payment, create and pay invoice
     *
     * @return bool
     */
    public function processPayment()
    {
        try {
            $orderIncrementId = $this->getIpnData('merchant_invoice');
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
            $invoice = $order->prepareInvoice()
                ->setTransactionId(1)
                ->addComment('Payment of ' . $this->getIpnData('amount') . ' received by CoinJar Checkout (' . $this->getIpnData('ipn_digest') . ')')
                ->register()
                ->pay();

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();

            return true;
        } catch (Exception $e) {

        }
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