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

class ZerebroInternet_Barzahlen_Model_Api extends ZerebroInternet_Barzahlen_Model_Api_Abstract {

  protected $_shopId; //!< merchants shop id
  protected $_paymentKey; //!< merchants payment key
  protected $_language = 'de'; //!< langauge code
  protected $_sandbox = false; //!< sandbox settings
  protected $_madeAttempts = 0; //!< performed attempts

  /**
   * Constructor. Sets basic settings. Adjusted for Magento
   *
   * @param array $arguements array with settings
   */
  public function __construct(array $arguements) {

    $this->_shopId = $arguements['shopId'];
    $this->_paymentKey = $arguements['paymentKey'];
    $this->_sandbox = $arguements['sandbox'];
  }

  /**
   * Sets the language for payment / refund slip.
   *
   * @param string $language Langauge Code (ISO 639-1)
   */
  public function setLanguage($language = 'de') {

    $this->_language = $language;
  }

  /**
   * Handles request of all kinds.
   *
   * @param Barzahlen_Request $request request that should be made
   */
  public function handleRequest($request) {

    $requestArray = $request->buildRequestArray($this->_shopId, $this->_paymentKey, $this->_language);
    $this->_debug("API: Sending request array to Barzahlen.", $requestArray);
    $xmlResponse = $this->_connectToApi($requestArray, $request->getRequestType());
    $this->_debug("API: Received XML response from Barzahlen.", $xmlResponse);
    $request->parseXml($xmlResponse, $this->_paymentKey);
    $this->_debug("API: Parsed XML response and returned it to request object.", $request->getXmlArray());
  }

  /**
   * Connects to Barzahlen Api as long as there's a xml response or maximum attempts are reached.
   *
   * @param array $requestArray array with the information which shall be send via POST
   * @param string $requestType type for request
   * @return xml response from Barzahlen
   */
  protected function _connectToApi(array $requestArray, $requestType) {

    $this->_madeAttempts++;

    try {
      return $this->_sendRequest($requestArray, $requestType);
    }
    catch (Exception $e) {
      if ($this->_madeAttempts >= self::MAXATTEMPTS) {
        Mage::throwException($e->getMessage());
      }
      return $this->_connectToApi($requestArray, $requestType);
    }
  }

  /**
   * Send the information via HTTP POST to the given domain. A xml as anwser is expected.
   * SSL is required for a connection to Barzahlen.
   *
   * @param array $requestArray array with the information which shall be send via POST
   * @param string $requestType type of request
   * @return xml response from Barzahlen
   */
  protected function _sendRequest(array $requestArray, $requestType) {

    $callDomain = $this->_sandbox ? self::APIDOMAINSANDBOX.$requestType : self::APIDOMAIN.$requestType;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $callDomain);
    curl_setopt($ch, CURLOPT_POST, count($requestArray));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestArray);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/Api/certs/ca-bundle.crt');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, 1.1);
    $return = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if($error != '') {
      Mage::throwException('Error during cURL: ' . $error);
    }

    return $return;
  }
}
?>