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
 * @copyright   Copyright (c) 2013 Zerebro Internet GmbH (http://www.barzahlen.de)
 * @author      Martin Seener
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_ResendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Handles payment slip resend requests.
     */
    public function paymentAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        $transactionId = $order->getPayment()->getAdditionalInformation('transaction_id');

        if (Mage::getSingleton('barzahlen/resend')->resend($transactionId)) {
            $this->_getSession()->addSuccess($this->__('bz_adm_resend_payment_success'));
        } else {
            $this->_getSession()->addError($this->__('bz_adm_resend_error'));
        }

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $id));
    }

    /**
     * Handles refund slip resend requests.
     */
    public function refundAction()
    {
        $id = $this->getRequest()->getParam('creditmemo_id');
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($id);

        $transactionId = $creditmemo->getTransactionId();

        if (Mage::getSingleton('barzahlen/resend')->resend($transactionId)) {
            $this->_getSession()->addSuccess($this->__('bz_adm_resend_refund_success'));
        } else {
            $this->_getSession()->addError($this->__('bz_adm_resend_error'));
        }

        $this->_redirect('adminhtml/sales_creditmemo/view', array('creditmemo_id' => $id));
    }
}
