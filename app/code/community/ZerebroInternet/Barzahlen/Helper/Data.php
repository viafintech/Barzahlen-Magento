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

class ZerebroInternet_Barzahlen_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Logs errors to the given log file.
     *
     * @param string $error_msg explaination of the occurred error
     * @param array $error_data corresponding data
     */
    public function bzLog($error_msg, array $error_data = array())
    {
        Mage::log($error_msg . " - " . serialize($error_data) . "\r\r", null, "barzahlen.log");
    }
}
