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

class ZerebroInternet_Barzahlen_Model_Api_Version
{
    /**
     * @var string
     */
    private $pluginVersion = null;

    /**
     * @var string
     */
    private $pluginUrl = null;

    /**
     * Kicks off the plugin check.
     *
     * @param string $shopsystem used shop system
     * @param string $shopsystemVersion used shop system version
     * @param string $pluginVersion current plugin version
     * @return boolean | string
     */
    public function isNewVersionAvailable($shopId, $shopsystem, $shopsystemVersion, $pluginVersion)
    {
        $transArray = array(
            'shop_id' => $shopId,
            'shopsystem' => $shopsystem,
            'shopsystem_version' => $shopsystemVersion,
            'plugin_version' => $pluginVersion
        );
        $this->requestVersion($transArray);

        if ($this->result == 0 && $this->pluginVersion != null && $pluginVersion != $this->pluginVersion) {
            return true;
        }

        return false;
    }

    /**
     * Requests the current version and parses the xml.
     *
     * @param array $transArray
     * @return boolean | string
     */
    protected function requestVersion(array $transArray)
    {
        $curl = $this->prepareRequest($transArray);
        $xmlResponse = $this->sendRequest($curl);

        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlResponse);

        $this->result = $domDocument->getElementsByTagName("result")->item(0)->nodeValue;
        if ($this->result != 0) {
            $errorMessage = $domDocument->getElementsByTagName("error-message")->item(0)->nodeValue;
            Mage::log('barzahlen/versioncheck: Error during cURL - ' . $errorMessage . "\r\r", null, "barzahlen.log");
        }

        $this->pluginVersion = $domDocument->getElementsByTagName("plugin-version")->item(0)->nodeValue;
        $this->pluginUrl = $domDocument->getElementsByTagName("plugin-url")->item(0)->nodeValue;
    }

    /**
     * Prepares the curl request.
     *
     * @param array $requestArray array with the information which shall be send via POST
     * @return cURL handle object
     */
    protected function prepareRequest(array $requestArray)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://plugincheck.barzahlen.de/check');
        curl_setopt($curl, CURLOPT_POST, count($requestArray));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestArray);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/certs/ca-bundle.crt');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, 1.1);
        return $curl;
    }

    /**
     * Sends the information via HTTP POST to the given domain expecting a response.
     *
     * @return cURL handle object
     * @return xml response from Barzahlen
     */
    protected function sendRequest($curl)
    {
        $return = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error != '') {
            Mage::log('barzahlen/versioncheck: Error during cURL - ' . $error . "\r\r", null, "barzahlen.log");
        }

        return $return;
    }

    /**
     * Returns the current plugin version.
     *
     * @return string
     */
    public function getNewPluginVersion()
    {
        return $this->pluginVersion;
    }

    /**
     * Returns the current plugin url.
     *
     * @return string
     */
    public function getNewPluginUrl()
    {
        return $this->pluginUrl;
    }
}
