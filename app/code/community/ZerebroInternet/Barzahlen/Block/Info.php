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

class ZerebroInternet_Barzahlen_Block_Info extends Mage_Payment_Block_Info
{
    /**
     * Create the info block (Checkout Onepage Sidebar) and assign the template to it.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('barzahlen/info.phtml');
    }
}