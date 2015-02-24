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

class ZerebroInternet_Barzahlen_Model_Adminexceptions_Maxordertotal extends Mage_Core_Model_Config_Data
{
    /**
     * Checks the entered value before saving it to the configuration. Setting to default if an amount
     * greater than 1000 Euros was choosen.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function _beforeSave()
    {
        $maxordertotal = (double) $this->getValue();

        if ($maxordertotal >= 1000) {
            $translateMessage = Mage::helper('barzahlen')->__('bz_adm_maxordertotal_exception');
            Mage::getSingleton('adminhtml/session')->addError($translateMessage);
            Mage::helper('barzahlen')->bzLog('adminexceptions/maxordertotal: Value too high. Setting default.');
            $this->setValue('999.99');
        } elseif ($maxordertotal < 0) {
            $translateMessage = Mage::helper('barzahlen')->__('bz_adm_maxordertotal_exception');
            Mage::getSingleton('adminhtml/session')->addError($translateMessage);
            Mage::helper('barzahlen')->bzLog('adminexceptions/maxordertotal: Value too low. Setting default.');
            $this->setValue('999.99');
        }

        return parent::_beforeSave();
    }
}
