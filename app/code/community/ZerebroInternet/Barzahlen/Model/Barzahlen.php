<?php
/**
 * Barzahlen Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@barzahlen.de so we can send you a copy immediately.
 *
 * @category    ZerebroInternet
 * @package     ZerebroInternet_Barzahlen
 * @copyright   Copyright (c) 2012 Zerebro Internet GmbH (http://www.barzahlen.de)
 * @author      Martin Seener
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_Model_Barzahlen extends Mage_Payment_Model_Method_Abstract {

  const PAYMENT_CODE = 'barzahlen'; //!< payment code
  const DEFAULT_LOG_FILE = 'barzahlen.log'; //!< file for all log and debug data
  protected $_code = 'barzahlen'; //!< module code
  protected $_paymentMethod = 'barzahlen'; //!< payment method name
  protected $_formBlockType = 'barzahlen/form'; //!< formular block
  protected $_infoBlockType = 'barzahlen/info'; //!< information block
  protected $_redirectProcessingUrl = 'barzahlen/checkout/processing'; //!< processing url

  /**
   * Availability options
   *
   * @var boolean
   */
  protected $_canRefund               = true; //!< Barzahlen transactions can be refunded
  protected $_canRefundInvoicePartial = true; //!< refunds can be done partial
  protected $_canUseInternal          = true; //!< can be used in admin panel checkout
  protected $_canUseCheckout          = true; //!< can be used as method on onepage checkout
  protected $_canUseForMultishipping  = true; //!< can be used as method on multipage checkout

  /**
   * Barzahlen payment state possible values
   *
   * @var string
   */
  const PAYMENTSTATE_PENDING          = 'pending'; //!< pending state (new order)
  const PAYMENTSTATE_PAID             = 'paid'; //!< paid state (processing order)
  const PAYMENTSTATE_EXPIRED          = 'expired'; //!< expired state (canceled order)
  const PAYMENTSTATE_REFUND_PENDING   = 'refund_pending'; //!< refund pending state (credit memo OPEN)
  const PAYMENTSTATE_REFUND_COMPLETED = 'refund_completed'; //!< refund completed state (credit memo REFUNDED)
  const PAYMENTSTATE_REFUND_EXPIRED   = 'refund_expired'; //!< refund expired state (credit memo CANCELED)
  const PAYMENTSTATE_TEXT_BLOCKS      = 'bz_frnt_ipn_'; //!< text block prefix

  /**
   * Constructs Barzahlen API object.
   *
   * @return Barzahlen API object
   */
  public function getBarzahlenApi() {

    $shopId = $this->getConfigData('shop_id');
    $paymentKey = $this->getConfigData('payment_key');
    $sandbox = $this->getConfigData('sandbox');
    $barzahlenApi = Mage::getModel('barzahlen/api', array('shopId' => $shopId, 'paymentKey' => $paymentKey, 'sandbox' => $sandbox));
    $barzahlenApi->setLanguage(substr((Mage::getSingleton('core/locale')->getLocaleCode()),0,2));

    if($this->getConfigData('debug')) {
      $barzahlenApi->setDebug(true, self::DEFAULT_LOG_FILE);
    }

    return $barzahlenApi;
  }

  /**
   * Redirect URL for checkouts using Barzahlen Payment Method - instead of onepage checkout success page.
   *
   * @return string with URL
   */
  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl($this->_redirectProcessingUrl);
  }
}
?>