<?php
/**
 * Barzahlen Payment Module SDK for Magento
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
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL-3.0)
 */

abstract class ZerebroInternet_Barzahlen_Model_Api_Abstract {

  const APIDOMAIN = 'https://api.barzahlen.de/v1/transactions/'; //!< call domain (productive use)
  const APIDOMAINSANDBOX = 'https://api-sandbox.barzahlen.de/v1/transactions/'; //!< sandbox call domain

  const HASHALGO = 'sha512'; //!< hash algorithm
  const SEPARATOR = ';'; //!< separator character
  const MAXATTEMPTS = 2; //!< maximum of allowed connection attempts

  protected $_debug = false; //!< debug mode on / off
  protected $_logFile; //!< log file for debug output

  /**
   * Sets debug settings.
   *
   * @param boolean $debug debug mode on / off
   * @param string $logFile position of log file
   */
  final public function setDebug($debug, $logFile) {
    $this->_debug = $debug;
    $this->_logFile = $logFile;
  }

  /**
   * Write debug message to log file. Adjusted for Magento
   *
   * @param string $message debug message
   * @param array $data related data (optional)
   */
  final protected function _debug($message, $data = array()) {

    if($this->_debug == true) {
      Mage::log($message.' - '.serialize($data), null, $this->_logFile);
    }
  }

  /**
   * Generates the hash for the request array.
   *
   * @param array $requestArray array from which hash is requested
   * @param string $key private key depending on hash type
   * @return hash sum
   */
  final protected function _createHash(array $hashArray, $key) {

    $hashArray[] = $key;
    $hashString = implode(self::SEPARATOR, $hashArray);
    return hash(self::HASHALGO, $hashString);
  }
}
?>