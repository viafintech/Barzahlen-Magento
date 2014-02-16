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

class ZerebroInternet_Barzahlen_Model_Adminexceptions_Notificationkey extends Mage_Core_Model_Config_Data
{
    /**
     * Checks the entered value before saving it to the configuration. Setting to old value if string
     * length is lower than 1.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function _beforeSave()
    {
        $notificationkey = $this->getValue();

        if (strlen($notificationkey) < 1) {
            $translateMessage = Mage::helper('barzahlen')->__('bz_adm_notificationkey_exception');
            Mage::getSingleton('adminhtml/session')->addError($translateMessage);
            Mage::helper('barzahlen')->bzLog('adminexceptions/notificationkey: Notification key too small. Setting last one');
            $this->setValue($this->getOldValue());
        }

        return parent::_beforeSave();
    }
}
