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

class ZerebroInternet_Barzahlen_Model_Observer
{
    public function addGridButtons($observer)
    {
        $block = $observer->getBlock();
        $type = $block->getType();

        if($type == 'adminhtml/sales_order_view') {
            $order = $block->getOrder();
            $paymentMethod = $order->getPayment()->getMethod();

            if ($order->getId() && $order->canInvoice() && $paymentMethod == 'barzahlen') {
                $message = Mage::helper('sales')->__('bz_adm_resend_payment_slip_question');
                $block->addButton('payment_slip_resend', array(
                    'label' => Mage::helper('barzahlen')->__('bz_adm_resend_payment_slip'),
                    'onclick' => "confirmSetLocation('{$message}', '{$block->getUrl('barzahlen/resend/payment', array('order_id' => $order->getId()))}')"
                ), 0, 100, 'header');
            }
        } elseif ($type == 'adminhtml/sales_order_creditmemo_view'){
            $creditMemo = $block->getCreditmemo();
            $paymentMethod = $creditMemo->getOrder()->getPayment()->getMethod();

            if ($creditMemo->getId() && $paymentMethod == 'barzahlen') {
                $message = Mage::helper('sales')->__('bz_adm_resend_refund_slip_question');
                $block->addButton('refund_slip_resend', array(
                    'label' => Mage::helper('barzahlen')->__('bz_adm_resend_refund_slip'),
                    'onclick' => "confirmSetLocation('{$message}', '{$block->getUrl('barzahlen/resend/refund', array('creditmemo_id' => $creditMemo->getId()))}')"
                ), 0, 100, 'header');
            }
        }
    }

    public function insertInfotext($observer)
    {
        $block = $observer->getBlock();
        $type = $block->getType();
        $session = Mage::getSingleton('checkout/session');

        if (($type == 'checkout/success' || $type == 'checkout/onepage_success') && $session->getData('barzahlen_infotext')) {
            $child = clone $block;
            $child->setType('barzahlen/success');
            $block->setChild('child', $child);
            $block->setTemplate('barzahlen/success.phtml');
            $block->setData('barzahlen_infotext', $session->getData('barzahlen_infotext', true));
        }
    }

    /**
     * Checks if a update request is necessary and performs it if so.
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkVersion($observer)
    {
        $user = $observer->getUser();
        $extra = unserialize($user->getExtra());

        $lastCheck = Mage::getStoreConfig('payment/barzahlen/last_check');

        if (isset($extra['configState']['payment_barzahlen']) && $extra['configState']['payment_barzahlen'] == 1) {
            if ($lastCheck == null || $lastCheck < strtotime("-1 week")) {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('payment/barzahlen/last_check', time(), 'default', 0);
                Mage::getConfig()->cleanCache();

                $barzahlen = Mage::getSingleton('barzahlen/barzahlen');
                $check = Mage::getSingleton('barzahlen/api_version');
                $shopId = $barzahlen->getConfigData('shop_id') != null ? $barzahlen->getConfigData('shop_id') : $barzahlen->getConfigData('shop_id', 1);
                if ($check->isNewVersionAvailable($shopId, 'Magento', Mage::getVersion(), (string) Mage::getConfig()->getNode()->modules->ZerebroInternet_Barzahlen->version)) {
                    Mage::getSingleton('core/session')->addNotice(sprintf(Mage::helper('barzahlen')->__('bz_adm_now_available'), $check->getNewPluginVersion(), $check->getNewPluginUrl()));
                }
            }
        }
    }
} 