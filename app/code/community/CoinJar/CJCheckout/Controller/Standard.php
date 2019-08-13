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

class CoinJar_CJCheckout_Controller_Standard extends CoinJar_CJCheckout_Controller_Abstract
{
    protected $_paymentMethodCode   = CoinJar_CJCheckout_Model_Config::METHOD_CJ_STANDARD;

    /**
     * Create the order and redirect to the coinjar website for payment.
     *
     * @return void
     */
    public function redirectAction()
    {
        $client = $this->_getApi()->buildClient();
        $orderId = $this->_getCheckoutSession()->getLastRealOrderId();
        $postParams = $this->_getApi()->buildRequestParams($orderId, $orderId);
        $client->setParameterPost('order', $postParams);

        $errorMessage = "";

        try{
            $response = $client->request();
            if ($response->isSuccessful()) {
                $responseBody = $response->getBody();
                $responseJson = Mage::helper('core')->jsonDecode($responseBody);

                $uuid = $responseJson["order"]["uuid"];
                $this->_getCheckoutSession()->setData('orderUuid', $uuid);
                $url = $this->_getConfig()->getCoinjarOrderUrl() . '/' . $uuid;

                Mage::getSingleton('core/cookie')->set('coinjarOrder', $url . '||' . (time()+900), 900, '/', null, null, false);

                // Redirect to CoinJar
                $this->_redirectUrl($url);
            } else {
                $errorMessage = 'CoinJar Server returned error with code ' . $response->getStatus();
                if ($response->isSuccessful()) {
                    $errorMessage .= ', ' . $response.getRawBody();
                }
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            $errorMessage = 'Exception: ' . $e->getMessage();
            Mage::helper('cjcheckout')->log('Error occurred while accessing the CoinJar Order API: ' . $errorMessage);
            Mage::getSingleton('checkout/session')->addError('An unrecoverable error occurred while processing your payment information. Please contact the site administrator. ' . $errorMessage);

            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Notification point for the CoinJar IPN
     *
     * @return void
     */
    public function notifyAction()
    {
        if (Mage::helper('cjcheckout/checkout')->ipnEnabled()) {
            $postData = $this->getRequest()->getPost();
            if (!$postData && Mage::helper('cjcheckout/checkout')->debugIpn()) {
                $postData = $this->getRequest()->getParams();
            }
            Mage::helper('cjcheckout')->log('IPN request: ' . http_build_query($postData));
            $this->_getIpn()->setIpnData($postData);
            if (!$this->_getIpn()->verify()) {
                // Ignore invalid IPN
                return;
            }
            $this->_getIpn()->processPayment();
            Mage::helper('cjcheckout')->log('Successful IPN for order: ' . $postData['uuid']);
        }
        return $this;
    }

    /**
     * When a customer cancels payment on the CoinJar site
     *
     * @return void
     */
    public function cancelAction()
    {
        Mage::helper('cjcheckout')->log('Controller load: cjcheckout/standard/cancel');
        Mage::helper('cjcheckout')->log('POST data: ' . http_build_query($_POST));
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getData('coinjar_quote_id', true));
        if ($session->getLastRealOrderId()) {
            Mage::getSingleton('core/cookie')->delete('coinjarOrder', '/', null, null, false);
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('cjcheckout/checkout')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * When customer returns from CoinJar, create the order if it doesn't already exist.
     *
     * @return void
     */
    public function  successAction()
    {
        Mage::helper('cjcheckout')->log('Controller load: cjcheckout/standard/success');

        if ($this->_getQuote()->getIsActive()) {
            $this->_getQuote()->setTotalsCollectedFlag(false);
            $this->_getQuote()->collectTotals();
            $order = $this->_getOnepage()->saveOrder();
        }
        
        Mage::getSingleton('core/cookie')->delete('coinjarOrder', '/', null, null, false);
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }

}