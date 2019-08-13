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

class CoinJar_CJCheckout_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{
    protected $_code;
    protected $_config = null;
    protected $_formBlockType;

    /**
     * Availability options
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = false;
    protected $_canAuthorize                = false;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = false;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = false;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = false;
    protected $_canUseForMultishipping      = false;
    protected $_canFetchTransactionInfo     = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canReviewPayment            = false;
    protected $_isInitializeNeeded          = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('cjcheckout/abstract');
    }

    protected function _getConfig()
    {
        if (is_null($this->_config)) {
            $this->_config = Mage::getModel('cjcheckout/config', array($this->_code, Mage::app()->getStore()->getId()));
        }
        return $this->_config;
    }
}
