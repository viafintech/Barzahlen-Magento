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

class ZerebroInternet_Barzahlen_Model_Payment extends ZerebroInternet_Barzahlen_Model_Barzahlen {

  /**
   * Performs payment handling and order update.
   */
  public function getTransactionId() {

    $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

    if(!$this->_validateOrder($order)) {
      return;
    }

    $orderAddress = $order->getBillingAddress();
    $customerEmail = $order->getCustomerEmail();
    $customerStreetNr = $orderAddress->getData("street");
    $customerZipcode = $orderAddress->getData("postcode");
    $customerCity = $orderAddress->getData("city");
    $customerCountry = $orderAddress->getData("country_id");
    $amount = Mage::getSingleton('core/store')->roundPrice($order->getGrandTotal(), 2);
    $currency = $order->getOrderCurrencyCode();
    $payment = Mage::getModel('barzahlen/api_request_payment',
      array('customerEmail' => $customerEmail, 'customerStreetNr' => $customerStreetNr,
            'customerZipcode' => $customerZipcode, 'customerCity' => $customerCity,
            'customerCountry' => $customerCountry, 'orderId' => $orderId, 'amount' => $amount, 'currency' => $currency));

    // filter the 3 custom vars and escape them for HTML compliance
    $tcHelper = Mage::getModel('core/email_template_filter');
    $customVar0 = $tcHelper->filter($this->getConfigData('custom_var_0'));
    $customVar1 = $tcHelper->filter($this->getConfigData('custom_var_1'));
    $customVar2 = $tcHelper->filter($this->getConfigData('custom_var_2'));
    $payment->setCustomVar($customVar0, $customVar1, $customVar2);

    try {
      Mage::getSingleton('barzahlen/barzahlen')->getBarzahlenApi()->handleRequest($payment);
    }
    catch (Exception $e) {
      Mage::helper('barzahlen')->bzLog($e);
      $this->_registerFailure($order);
      throw $e;
    }

    if($payment->isValid()) {
      $this->_registerSuccess($order, $payment->getXmlArray());
    }
    else {
      $this->_registerFailure($order);
    }
  }

  /**
   * Validates, that the order exists, Barzahlen was choosen and the order wasn't paid yet.
   *
   * @param Mage_Sales_Model_Order $order
   * @return boolean
   */
  protected function _validateOrder($order) {

    // check if order exisits
    if (!$order->getId()) {
      Mage::helper('barzahlen')->bzLog('controllers/checkout: order not existing, aborting/redirecting');
      return false;
    }

    // get order payment and its information
    $payment = $order->getPayment();
    $code = $payment->getMethod();
    $transactionId = $payment->getAdditionalInformation('transaction_id');

    // check that Barzahlen was choosen
    if($code != self::PAYMENT_CODE) {
      $errorData = array($order->getId(), $code);
      Mage::helper('barzahlen')->bzLog('controllers/checkout: Barzahlen was not choosen as payment method', $errorData);
      return false;
    }

    // check that order isn't paid yet and is still payable
    if($transactionId != '' || $order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
      $errorData = array($order->getId(), $transactionId);
      Mage::helper('barzahlen')->bzLog('controllers/checkout: order already got an transaction id', $errorData);
      return false;
    }

    return true;
  }

  /**
   * Sets all information for the failure page.
   *
   * @param Mage_Sales_Model_Order $order
   */
  protected function _registerFailure($order) {

    $session = Mage::getSingleton('checkout/session');
    $session->setResponse('400');
    $session->getQuote()->setIsActive(false)->save();

    $errorMsg = Mage::helper('barzahlen')->__('bz_frnt_ipn_denied');
    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $errorMsg);
    $order->save();
  }

  /**
   * Sets all information for the success page.
   *
   * @param Mage_Sales_Model_Order $order
   * @param array $xmlArray array with xml response information
   */
  protected function _registerSuccess($order, array $xmlArray) {

    $session = Mage::getSingleton('checkout/session');
    $session->setResponse('200');
    $session->setBzPaymentSlipLink($xmlArray['payment-slip-link']);
    $session->setBzExpirationNote($xmlArray['expiration-notice']);
    $session->setBzInfotext1($xmlArray['infotext-1']);
    $session->setBzInfotext2($xmlArray['infotext-2']);
    $session->getQuote()->setIsActive(false)->save();

    $order->getPayment()->setAdditionalInformation('transaction_id', $xmlArray['transaction-id']);
    $order->sendNewOrderEmail();
    $order->addStatusHistoryComment(Mage::helper('barzahlen')->__('bz_frnt_ipn_pending'), Mage::getModel('barzahlen/barzahlen')->getConfigData('order_status'));
    $order->save();
  }
}
?>