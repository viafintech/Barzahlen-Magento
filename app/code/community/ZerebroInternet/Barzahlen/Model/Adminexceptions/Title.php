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

class ZerebroInternet_Barzahlen_Model_Adminexceptions_Title extends Mage_Core_Model_Config_Data
{
    /**
     * Checks the entered value before saving it to the configuration. Setting to default if string
     * length is lower than 1.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        $title = $this->getValue();

        if (strlen($title) < 1) {
            $translateMessage = Mage::helper('barzahlen')->__('bz_adm_co_exception');
            Mage::getSingleton('adminhtml/session')->addError($translateMessage);
            Mage::helper('barzahlen')->bzLog('adminexceptions/title: Empty string given. Setting default title.');
            $this->setValue('Barzahlen');
        }

        return parent::save();
    }
}
