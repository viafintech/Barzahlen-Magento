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
 * @copyright   Copyright (c) 2012 Zerebro Internet GmbH (http://www.barzahlen.de)
 * @author      Martin Seener
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

class ZerebroInternet_Barzahlen_Helper_Data extends Mage_Core_Helper_Abstract {

  const DEFAULT_LOG_FILE = 'barzahlen.log'; //!< file for all log and debug data

  /**
   * Logs errors to the given log file.
   *
   * @param string $error_msg explaination of the occurred error
   * @param array $error_data corresponding data
   */
  public function bzLog($error_msg, array $error_data = array()) {
    Mage::log($error_msg . " - " . serialize($error_data) . "\r\r", null, self::DEFAULT_LOG_FILE);
  }
}
?>