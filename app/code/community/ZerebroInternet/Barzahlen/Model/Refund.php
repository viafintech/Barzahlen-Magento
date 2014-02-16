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

class ZerebroInternet_Barzahlen_Model_Refund extends Mage_Core_Model_Abstract {

  /**
   * Catches the credit memo process and requests a refund by connecting to Barzahlen.
   *
   * @param Varien_Event_Observer $observer
   * @return Varien_Event_Observer with updated data
   */
  public function refundObserver($observer) {

    $creditmemo = $observer->getEvent()->getcreditmemo();
    $order = $creditmemo->getOrder();

    if(!$order->getId()) {
      Mage::helper('barzahlen')->bzLog('model/refund: no valid order choosen');
      Mage::throwException(Mage::helper('barzahlen')->__('bz_adm_refund_error'));
    }

    if($order->getPayment()->getMethod() != ZerebroInternet_Barzahlen_Model_Barzahlen::PAYMENT_CODE) {
      return $observer;
    }

    $transactionId = $order->getPayment()->getAdditionalInformation('transaction_id');
    $amount = round($creditmemo->getGrandTotal(),2);
    $currency = $order->getOrderCurrencyCode();
    $refund = Mage::getModel('barzahlen/api_request_refund',
      array('transactionId' => $transactionId, 'amount' => $amount, 'currency' => $currency));

    try {
      Mage::getSingleton('barzahlen/barzahlen')->getBarzahlenApi()->handleRequest($refund);
    }
    catch (Exception $e) {
      Mage::helper('barzahlen')->bzLog($e);
    }

    if($refund->isValid() && $refund->getOriginTransactionId() == $transactionId) {
      $creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
      $creditmemo->setTransactionId($refund->getRefundTransactionId());
      $creditmemo->addComment(Mage::helper('barzahlen')->__('bz_adm_refund_success'), false, true);
      $creditmemo->save();
    }
    elseif($refund->getOriginTransactionId() != $transactionId) {
      Mage::helper('barzahlen')->bzLog('model/refund: refund transaction id doesn\'t match origin transaction id');
      Mage::throwException(Mage::helper('barzahlen')->__('bz_adm_refund_error'));
    }
    else {
      Mage::throwException(Mage::helper('barzahlen')->__('bz_adm_refund_error'));
    }

    return $observer;
  }
}