<?php
/**
 * Barzahlen Payment Module for Magento
 *
 * @category    ZerebroInternet
 * @package     ZerebroInternet_Barzahlen
 * @copyright   Copyright (c) 2015 Cash Payment Solutions GmbH (https://www.barzahlen.de)
 * @author      Alexander Diebler
 * @author      Martin Seener
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_Model_Barzahlen extends Mage_Payment_Model_Method_Abstract
{
    /**
     * module code
     *
     * @var string
     */
    protected $_code = 'barzahlen';

    /**
     * payment method name
     *
     * @var string
     */
    protected $_paymentMethod = 'barzahlen';

    /**
     * formular block
     *
     * @var string
     */
    protected $_formBlockType = 'barzahlen/form';

    /**
     * information block
     *
     * @var string
     */
    protected $_infoBlockType = 'barzahlen/info';

    /**
     * Availability options
     *
     * @var boolean
     */
    protected $_canOrder = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;

    /**
     * Constructs Barzahlen API object.
     *
     * @return Barzahlen API object
     */
    public function getBarzahlenApi()
    {
        $shopId = $this->getConfigData('shop_id');
        $paymentKey = $this->getConfigData('payment_key');
        $sandbox = $this->getConfigData('sandbox');

        $barzahlenApi = Mage::getModel('barzahlen/api', array('shopId' => $shopId, 'paymentKey' => $paymentKey, 'sandbox' => $sandbox));
        $barzahlenApi->setLanguage(substr((Mage::getSingleton('core/locale')->getLocaleCode()), 0, 2));
        $barzahlenApi->setDebug($this->getConfigData('debug'), 'barzahlen.log');
        $barzahlenApi->setUserAgent('Magento ' . Mage::getVersion() . ' / Plugin v1.3.0');

        return $barzahlenApi;
    }

    /**
     * Send payment request after order (backend / frontend).
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this|Mage_Payment_Model_Abstract
     * @throws Mage_Core_Exception
     */
    public function order(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $orderAddress = $order->getBillingAddress();

        $bzPayment = Mage::getModel(
            'barzahlen/api_request_payment',
            array(
                'customerEmail' => $order->getCustomerEmail(),
                'customerStreetNr' => $orderAddress->getData("street"),
                'customerZipcode' => $orderAddress->getData("postcode"),
                'customerCity' => $orderAddress->getData("city"),
                'customerCountry' => $orderAddress->getData("country_id"),
                'orderId' => $order->getRealOrderId(),
                'amount' => $amount,
                'currency' => $order->getOrderCurrencyCode()
            )
        );

        try {
            $this->getBarzahlenApi()->handleRequest($bzPayment);
            $payment->setAdditionalInformation('transaction_id', $bzPayment->getTransactionId())
                    ->setTransactionId($bzPayment->getTransactionId())
                    ->setIsTransactionClosed(0);
            $session = Mage::getSingleton('checkout/session');
            $session->setData('barzahlen_infotext', $bzPayment->getInfotext1());
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e);
            Mage::throwException(Mage::helper('barzahlen')->__('bz_frnt_error'));
        }

        return $this;
    }

    /**
     * Sends resend request to Barzahlen and returns if request was successful.
     *
     * @param integer $transactionId Barzahlen Transaction ID
     * @return boolean
     */
    public function resendSlip($transactionId)
    {
        $bzResend = Mage::getModel(
            'barzahlen/api_request_resend',
            array(
                'transactionId' => $transactionId
            )
        );

        try {
            $this->getBarzahlenApi()->handleRequest($bzResend);
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e);
        }

        return $bzResend->isValid();
    }

    /**
     * Trigger online refund action from admin panel.
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this|Mage_Payment_Model_Abstract
     * @throws Mage_Core_Exception
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $bzRefund = Mage::getModel(
            'barzahlen/api_request_refund',
            array(
                'transactionId' => $payment->getAdditionalInformation('transaction_id'),
                'amount' => $amount,
                'currency' => $payment->getOrder()->getOrderCurrencyCode()
            )
        );

        try {
            $this->getBarzahlenApi()->handleRequest($bzRefund);
            $payment->setTransactionId($bzRefund->getRefundTransactionId());
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e);
            if (strpos($e->getMessage(), 'refund declined')) {
                Mage::throwException(Mage::helper('barzahlen')->__('bz_adm_refund_declined'));
            } else {
                Mage::throwException(Mage::helper('barzahlen')->__('bz_adm_refund_error'));
            }
        }

        return $this;
    }

    /**
     * Cancel payment slip after order cancellation.
     *
     * @param $data
     */
    public function cancelPaymentSlip($data)
    {
        $payment = $data->getOrder()->getPayment();

        if ($payment->getMethod() != $this->_code) {
            return;
        }

        $bzCancel = Mage::getModel(
            'barzahlen/api_request_cancel',
            array(
                'transactionId' => $payment->getAdditionalInformation('transaction_id')
            )
        );

        try {
            $this->getBarzahlenApi()->handleRequest($bzCancel);
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e);
        }
    }
}
