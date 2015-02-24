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

class ZerebroInternet_Barzahlen_Model_Adminexceptions_Allspecificcountries extends Mage_Core_Model_Config_Data
{
    /**
     * Checks the entered value before saving it to the configuration. Setting to default (DE only) if
     * another country than Germany was choosen.
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        if ($this->getValue() != '1') {
            $translateMessage = Mage::helper('barzahlen')->__('bz_adm_specificcountry_exception');
            Mage::getSingleton('adminhtml/session')->addError($translateMessage);
            Mage::helper('barzahlen')->bzLog('adminexceptions/country: Setting DE as allowed country');
            $this->setValue(1);
        }

        return parent::save();
    }
}
