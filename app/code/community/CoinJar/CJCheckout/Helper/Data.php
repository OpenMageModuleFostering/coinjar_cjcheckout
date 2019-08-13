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

class CoinJar_CJCheckout_Helper_Data extends Mage_Payment_Helper_Data
{

    /**
     * Used only in development. Remove before production.
     *
     * @return  array
     */
    public function getDebugBacktrace()
    {
        $stacks = debug_backtrace();
        $result = array();
        foreach ($stacks as $_stack) {
            if (!isset($_stack['file'])) $_stack['file'] = '[PHP Kernel]';
            if (!isset($_stack['line'])) $_stack['line'] = '';
            $result[] = array(
                'file' => $_stack["file"],
                'line' => $_stack["line"],
                'function' => $_stack["function"]
            );
        }
        return $result;
    }

    /**
     * Used to determine if CoinJar debug mode is enabled in the System Configuration 
     *
     * @return  bool
     */
    public function debug()
    {
        return (bool) Mage::getStoreConfig('payment_services/coinjar/debuglog');
    }

    /**
     * Log coinjar specific messages in the coinjar log only when in debug mode.
     *
     * @return  CoinJar_CJCheckout_Helper_Data
     */
    public function log($message)
    {
        if (Mage::helper('cjcheckout')->debug()) {
            Mage::log($message, null, 'coinjar.log');
        }
        return $this;
    }
}