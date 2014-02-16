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

class ZerebroInternet_Barzahlen_Block_Adminhtml_Sales_Order_Creditmemo_View extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_View
{
    public function __construct()
    {
        parent::__construct();
        $creditMemo = $this->getCreditmemo();
        $paymentMethod = $creditMemo->getOrder()->getPayment()->getMethodInstance()->getCode();

        if ($creditMemo->getId() && $creditMemo->getState() == $creditMemo::STATE_OPEN && $paymentMethod == 'barzahlen') {
            $message = Mage::helper('sales')->__('bz_adm_resend_refund_slip_question');
            $this->_addButton('refund_slip_resend', array(
                'label' => Mage::helper('barzahlen')->__('bz_adm_resend_refund_slip'),
                'onclick' => "confirmSetLocation('{$message}', '{$this->getResendPaymentUrl($creditMemo->getId())}')"
                    ), 0, 100, 'header');
        }
    }

    public function getResendPaymentUrl($creditMemoId)
    {
        return $this->getUrl('barzahlen/resend/refund', array('creditmemo_id' => $creditMemoId));
    }
}
