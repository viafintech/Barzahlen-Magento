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

class ZerebroInternet_Barzahlen_Model_Resend extends ZerebroInternet_Barzahlen_Model_Barzahlen
{
    /**
     * Sends resend request to Barzahlen and returns if request was successful.
     *
     * @param integer $transactionId Barzahlen Transaction ID
     * @return boolean
     */
    public function resend($transactionId)
    {
        $resend = Mage::getModel('barzahlen/api_request_resend', array('transactionId' => $transactionId));

        try {
            Mage::getSingleton('barzahlen/barzahlen')->getBarzahlenApi()->handleRequest($resend);
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog($e);
        }

        return $resend->isValid();
    }
}
