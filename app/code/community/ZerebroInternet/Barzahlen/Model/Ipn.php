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

class ZerebroInternet_Barzahlen_Model_Ipn {

  protected $_receivedData = array(); //!< received data from the server
  protected $_order; //!< corresponding order (Mage_Sales_Model_Order)
  protected $_creditmemo; //!< corresponding credit memo (Mage_Sales_Model_Order_Creditmemo)

  /**
   * Checks received data and validates hash.
   *
   * @param string $uncleanData received data
   * @return TRUE if received get array is valid and hash could be confirmed
   * @return FALSE if an error occurred
   */
  public function sendResponseHeader($ipnData) {

    $barzahlen = Mage::getModel('barzahlen/barzahlen');
    $shopId = $barzahlen->getConfigData('shop_id');
    $notificationKey = $barzahlen->getConfigData('notification_key');
    $notification = Mage::getModel('barzahlen/api_notification', array('shopId' => $shopId, 'notificationKey' => $notificationKey, 'receivedData' => $ipnData));

    try {
      $notification->validate();
    }
    catch (Exception $e) {
      Mage::helper('barzahlen')->bzLog($e, $ipnData);
    }

    if(!$notification->isValid()) {
      return false;
    }

    $this->_receivedData = $notification->getNotificationArray();
    return true;
  }

  /**
   * Parent function to update the database with all information.
   */
  public function updateDatabase() {

    $orderId = isset($this->_receivedData['origin_order_id']) ?  $this->_receivedData['origin_order_id'] : $this->_receivedData['order_id'];
    $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    if($this->_checkOrderInformation()) {
      if($this->_handleStateChange()) {
        $this->_order->save();
      }
    }
  }

  /**
   * Checks that there's an valid transaction id for the requested order.
   *
   * @return TRUE if transaction id was found and validated
   * @return FALSE if transaction id was not found or could not be validated
   */
  protected function _checkOrderInformation() {

    if(!$this->_order->getId()) {
        Mage::helper('barzahlen')->bzLog('controller/ipn: Unable to find the given order', $this->_receivedData);
        return false;
    }

    if(isset($this->_receivedData['order_id'])) {
      if($this->_order->getGrandTotal() != $this->_receivedData['amount']) {
        Mage::helper('barzahlen')->bzLog('controller/ipn: order total amount doesn\'t match', $this->_receivedData);
        return false;
      }
      if($this->_order->getOrderCurrencyCode() != $this->_receivedData['currency']) {
        Mage::helper('barzahlen')->bzLog('controller/ipn: order currency doesn\'t match', $this->_receivedData);
        return false;
      }
    }

    $transactionId = isset($this->_receivedData['origin_transaction_id']) ?  $this->_receivedData['origin_transaction_id'] : $this->_receivedData['transaction_id'];
    if($transactionId != $this->_order->getPayment()->getAdditionalInformation('transaction_id')) {
      Mage::helper('barzahlen')->bzLog('controller/ipn: Unable to find the transaction id in the given order', $this->_receivedData);
      return false;
    }

    return true;
  }

  /**
   * Calls the necessary method for the send state.
   */
  protected function _handleStateChange() {

    switch($this->_receivedData['state']) {
      case ZerebroInternet_Barzahlen_Model_Barzahlen::PAYMENTSTATE_PAID:
        $this->_processTransactionPaid();
        return true;
      case ZerebroInternet_Barzahlen_Model_Barzahlen::PAYMENTSTATE_EXPIRED:
        $this->_processTransactionExpired();
        return true;
      case ZerebroInternet_Barzahlen_Model_Barzahlen::PAYMENTSTATE_REFUND_COMPLETED:
        $this->_processRefundCompleted();
        return true;
      case ZerebroInternet_Barzahlen_Model_Barzahlen::PAYMENTSTATE_REFUND_EXPIRED:
        $this->_processRefundExpired();
        return true;
      default:
        Mage::helper('barzahlen')->bzLog('controller/ipn: Cannot handle payment state', $this->_receivedData);
        return false;
    }
  }

  /**
   * Creates invoice and sets order state for paid transactions.
   */
  protected function _processTransactionPaid() {

    if(!$this->_order->canInvoice()) {
      return;
    }

    Mage::getModel('sales/order_invoice_api')->create($this->_order->getIncrementId(), array(), $this->_createIpnComment(), false, true);
  }

  /**
   * Cancels an order after the period of ten days elasped.
   */
  protected function _processTransactionExpired() {
    $this->_order->registerCancellation($this->_createIpnComment(), false);
  }

  /**
   * Update order state after refund was completed successful.
   */
  protected function _processRefundCompleted() {
    if($this->_getCreditmemo()) {
      $this->_creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
      $this->_creditmemo->addComment($this->_createIpnComment(), false, true);
      $this->_creditmemo->save();
    }
  }

  /**
   * Sets an order be to completed after the period of thrity days for the refund elasped.
   */
  protected function _processRefundExpired() {
    if($this->_getCreditmemo()) {
      $this->_creditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED);
      $this->_creditmemo->addComment($this->_createIpnComment(), false, true);
      $this->_rollbackCreditmemo();
      $this->_creditmemo->save();
    }
  }

  /**
   * Get requested credit memo from the database.
   *
   * @return TURE if credit memo was found with refund_transaction_id
   * @return FALSE if no credit memo was found
   */
  protected function _getCreditmemo() {

    $creditmemos = $this->_order->getCreditmemosCollection();

    foreach($creditmemos as $creditmemo)
      if($creditmemo->getTransactionId() == $this->_receivedData['refund_transaction_id']) {
        if($creditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_OPEN) {
          Mage::helper('barzahlen')->bzLog('controller/ipn: credit memo already refunded / closed', $this->_receivedData);
          return false;
        }
        if($creditmemo->getGrandTotal() != $this->_receivedData['amount']) {
          Mage::helper('barzahlen')->bzLog('controller/ipn: credit memo total amount doesn\'t match', $this->_receivedData);
          return false;
        }
        if($creditmemo->getOrderCurrencyCode() != $this->_receivedData['currency']) {
          Mage::helper('barzahlen')->bzLog('controller/ipn: credit memo currency doesn\'t match', $this->_receivedData);
          return false;
        }
        $this->_creditmemo = $creditmemo;
        return true;
      }

    Mage::helper('barzahlen')->bzLog('controller/ipn: Unable to find requested creditmemo by refund_transaction_id', $this->_receivedData);
    return false;
  }

  /**
   * Creates the comment for the ipn according to store language.
   *
   * @return string with the ipn comment message
   */
  protected function _createIpnComment() {
    $paymentState = $this->_receivedData['state'];
    $message = Mage::helper('barzahlen')->__('bz_frnt_ipn_'.$paymentState);
    return $message;
  }

  /**
   * Performs rollback process when a refund is expired.
   */
  protected function _rollbackCreditmemo() {

    $this->_rollbackOrderItems();
    $this->_rollbackOrderStats();
    $this->_setNullforZero();

    $this->_order->getPayment()->cancelCreditmemo($this->_creditmemo);
  }

  /**
   * Sets back the quantity of the order items after a refund expired. Imitates
   * Mage_Sales_Model_Order_Item->cancel() since that function doesn't work the expected way.
   * (Magento v.1.6.1.0)
   */
  protected function _rollbackOrderItems() {
    foreach ($this->_creditmemo->getAllItems() as $item) {
      $item->getOrderItem()->setQtyRefunded($item->getOrderItem()->getQtyRefunded() - $item->getQty())->save();
    }
    $this->_creditmemo->save();
  }

  /**
   * Sets back order information to the state before the credit memo. Imitates
   * Mage_Sales_Model_Order_Creditmemo->cancel() since that function doesn't work the expected way.
   * (Magento v.1.6.1.0)
   */
  protected function _rollbackOrderStats() {

    $this->_order->setTotalOfflineRefunded($this->_order->getTotalOfflineRefunded() - $this->_creditmemo->getGrandTotal());
    $this->_order->setBaseTotalOfflineRefunded($this->_order->getBaseTotalOfflineRefunded() - $this->_creditmemo->getBaseGrandTotal());
    $this->_order->setTotalRefunded($this->_order->getTotalOfflineRefunded() + $this->_order->getTotalOnlineRefunded());
    $this->_order->setBaseTotalRefunded($this->_order->getBaseTotalOfflineRefunded() + $this->_order->getBaseTotalOnlineRefunded());
    $this->_order->setBaseSubtotalRefunded($this->_order->getBaseSubtotalRefunded() - $this->_creditmemo->getBaseSubtotal());
    $this->_order->setSubtotalRefunded($this->_order->getSubtotalRefunded() - $this->_creditmemo->getSubtotal());
    $this->_order->setBaseTaxRefunded($this->_order->getBaseTaxRefunded() - $this->_creditmemo->getBaseTaxAmount());
    $this->_order->setTaxRefunded($this->_order->getTaxRefunded() - $this->_creditmemo->getTaxAmount());
    $this->_order->setBaseShippingRefunded($this->_order->getBaseShippingRefunded() - $this->_creditmemo->getBaseShippingAmount());
    $this->_order->setShippingRefunded($this->_order->getShippingRefunded() - $this->_creditmemo->getShippingAmount());
  }

  /**
   * Replaces 0 by null to avoid automatic state setting by Mage_Sales_Model_Order after complete
   * refund rollback.
   */
  protected function _setNullforZero() {

    if($this->_order->getTotalOfflineRefunded() <= 0) $this->_order->setTotalOfflineRefunded(null);
    if($this->_order->getBaseTotalOfflineRefunded() <= 0) $this->_order->setBaseTotalOfflineRefunded(null);
    if($this->_order->getTotalOfflineRefunded() <= 0) $this->_order->setTotalRefunded(null);
    if($this->_order->getBaseTotalOfflineRefunded() <= 0) $this->_order->setBaseTotalRefunded(null);
    if($this->_order->getBaseSubtotalRefunded() <= 0) $this->_order->setBaseSubtotalRefunded(null);
    if($this->_order->getSubtotalRefunded() <= 0) $this->_order->setSubtotalRefunded(null);
    if($this->_order->getBaseTaxRefunded() <= 0) $this->_order->setBaseTaxRefunded(null);
    if($this->_order->getTaxRefunded() <= 0) $this->_order->setTaxRefunded(null);
    if($this->_order->getBaseShippingRefunded() <= 0) $this->_order->setBaseShippingRefunded(null);
    if($this->_order->getShippingRefunded() <= 0) $this->_order->setShippingRefunded(null);
  }
}
?>