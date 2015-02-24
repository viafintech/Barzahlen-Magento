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

class ZerebroInternet_Barzahlen_IpnController extends Mage_Core_Controller_Front_Action
{
    /**
     * Instantiate IPN model and pass IPN request to it. After successful hash validation and database update
     * HTTP header 200 is send to confirm callback.
     */
    public function indexAction()
    {
        try {
            $data = $this->getRequest()->getQuery();
            $ipnModel = Mage::getModel('barzahlen/ipn');

            if ($ipnModel->isDataValid($data) && $ipnModel->updateDatabase()) {
                $this->returnHeader(200);
            } else {
                $this->returnHeader(400);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    protected function returnHeader($code)
    {
        if ($code == 200) {
            header("HTTP/1.1 200 OK");
            header("Status: 200 OK");
        } else {
            header("HTTP/1.1 400 Bad Request");
            header("Status: 400 Bad Request");
            die();
        }
    }
}
