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

class CoinJar_CJCheckout_Model_Standard extends CoinJar_CJCheckout_Model_Abstract
{
    protected $_code                    = CoinJar_CJCheckout_Model_Config::METHOD_CJ_STANDARD;
    protected $_orderUuid               = null;
    protected $_formBlockType           = 'cjcheckout/standard_form';

    /**
     * Availability options
     */
    // protected $_isGateway                   = true;
    // protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    // protected $_canCapture                  = true;
    // protected $_canCapturePartial           = true;
    // protected $_canRefund                   = true;
    // protected $_canRefundInvoicePartial     = true;
    // protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    // protected $_canFetchTransactionInfo     = true;
    protected $_canCreateBillingAgreement   = false;
    // protected $_canReviewPayment            = true;
    protected $_isInitializeNeeded          = true;

    public function _construct()
    {
        parent::_construct();
        $this->_init('cjcheckout/standard');
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
          return Mage::getUrl('cjcheckout/standard/redirect', array('_secure' => true));
    }


    public function authorize(Varien_Object $payment, $amount) 
    {
        return $this;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

}
