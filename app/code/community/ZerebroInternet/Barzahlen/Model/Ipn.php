<?php
/**
 * Barzahlen Payment Module for Magento
 *
 * @category    ZerebroInternet
 * @package     ZerebroInternet_Barzahlen
 * @copyright   Copyright (c) 2014 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @author      Martin Seener
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_Model_Ipn
{
    /**
     * Received data from the server.
     *
     * @var array
     */
    protected $_receivedData = array();

    /**
     * Corresponding Order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Barzahlen payment state possible values
     *
     * @var string
     */
    const PAYMENTSTATE_PENDING = 'pending';
    const PAYMENTSTATE_PAID = 'paid';
    const PAYMENTSTATE_EXPIRED = 'expired';
    const PAYMENTSTATE_REFUND_PENDING = 'refund_pending';
    const PAYMENTSTATE_REFUND_COMPLETED = 'refund_completed';
    const PAYMENTSTATE_REFUND_EXPIRED = 'refund_expired';

    /**
     * Checks received data and validates hash.
     *
     * @param string $uncleanData received data
     * @return TRUE if received get array is valid and hash could be confirmed
     * @return FALSE if an error occurred
     */
    public function isDataValid($ipnData)
    {
        $barzahlen = Mage::getModel('barzahlen/barzahlen');
        $shopId = $barzahlen->getConfigData('shop_id');
        $notificationKey = $barzahlen->getConfigData('notification_key');
        $notification = Mage::getModel('barzahlen/api_notification', array('shopId' => $shopId, 'notificationKey' => $notificationKey, 'receivedData' => $ipnData));

        try {
            $notification->validate();
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e, $ipnData);
        }

        if (!$notification->isValid()) {
            return false;
        }

        $this->_receivedData = $notification->getNotificationArray();
        return true;
    }

    /**
     * Parent function to update the database with all information.
     */
    public function updateDatabase()
    {
        $orderId = isset($this->_receivedData['origin_order_id']) ? $this->_receivedData['origin_order_id'] : $this->_receivedData['order_id'];
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        if ($this->_checkOrderInformation() && $this->_handleStateChange()) {
            $this->_order->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks that there's an valid transaction id for the requested order.
     *
     * @return TRUE if transaction id was found and validated
     * @return FALSE if transaction id was not found or could not be validated
     */
    protected function _checkOrderInformation()
    {
        if (!$this->_order->getId()) {
            Mage::helper('barzahlen')->bzLog('controller/ipn: Unable to find the given order', $this->_receivedData);
            return false;
        }

        $transactionId = isset($this->_receivedData['origin_transaction_id']) ? $this->_receivedData['origin_transaction_id'] : $this->_receivedData['transaction_id'];
        if ($transactionId != $this->_order->getPayment()->getAdditionalInformation('transaction_id')) {
            Mage::helper('barzahlen')->bzLog('controller/ipn: Unable to find the transaction id in the given order', $this->_receivedData);
            return false;
        }

        return true;
    }

    /**
     * Calls the necessary method for the send state.
     */
    protected function _handleStateChange()
    {
        switch ($this->_receivedData['state']) {
            case self::PAYMENTSTATE_PAID:
                $this->_processTransactionPaid();
                return true;
            case self::PAYMENTSTATE_EXPIRED:
                $this->_processTransactionExpired();
                return true;
            case self::PAYMENTSTATE_REFUND_COMPLETED:
            case self::PAYMENTSTATE_REFUND_EXPIRED:
                return true;
            default:
                Mage::helper('barzahlen')->bzLog('controller/ipn: Cannot handle payment state', $this->_receivedData);
                return false;
        }
    }

    /**
     * Creates invoice and sets order state for paid transactions.
     */
    protected function _processTransactionPaid()
    {
        if(!$this->_order->canInvoice()) {
            return;
        }

        $payment = $this->_order->getPayment();
        $payment->setTransactionId($this->_receivedData['transaction_id'])
            ->setCurrencyCode($this->_receivedData['currency'])
            ->setPreparedMessage($this->_createIpnComment())
            ->setIsTransactionClosed(1)
            ->registerCaptureNotification($this->_receivedData['amount']);
    }

    /**
     * Cancels an order after the period for payment elapsed.
     */
    protected function _processTransactionExpired()
    {
        $payment = $this->_order->getPayment();
        $payment->getTransaction($this->_receivedData['transaction_id'])->close();
        $this->_order->registerCancellation($this->_createIpnComment(), false)->save();
        $this->_order->sendOrderUpdateEmail(true, $this->_createIpnComment());
    }

    /**
     * Creates the comment for the ipn according to store language.
     *
     * @return string with the ipn comment message
     */
    protected function _createIpnComment()
    {
        $message = Mage::helper('barzahlen')->__('bz_frnt_ipn_' . $this->_receivedData['state']);
        return $message;
    }
}
