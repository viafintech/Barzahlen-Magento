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

class ZerebroInternet_Barzahlen_Adminhtml_ResendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Handles payment slip resend requests.
     */
    public function paymentAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        $transactionId = $order->getPayment()->getAdditionalInformation('transaction_id');

        if (Mage::getSingleton('barzahlen/barzahlen')->resendSlip($transactionId)) {
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
        $creditMemo = Mage::getModel('sales/order_creditmemo')->load($id);

        $transactionId = $creditMemo->getTransactionId();

        if (Mage::getSingleton('barzahlen/barzahlen')->resendSlip($transactionId)) {
            $this->_getSession()->addSuccess($this->__('bz_adm_resend_refund_success'));
        } else {
            $this->_getSession()->addError($this->__('bz_adm_resend_error'));
        }

        $this->_redirect('adminhtml/sales_order_creditmemo/view', array('creditmemo_id' => $id));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/barzahlen_actions');
    }

}
