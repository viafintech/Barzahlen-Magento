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

class ZerebroInternet_Barzahlen_Model_Check extends ZerebroInternet_Barzahlen_Model_Api_Abstract
{
    /**
     * Checks if a update request is necessary and performs it if so.
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkObserver($observer)
    {
        $user = $observer->getUser();
        $extra = unserialize($user->getExtra());

        $lastCheck = Mage::getStoreConfig('payment/barzahlen/last_check');

        if (isset($extra['configState']['payment_barzahlen']) && $extra['configState']['payment_barzahlen'] == 1) {
            if ($lastCheck == null || $lastCheck < strtotime("-1 week")) {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('payment/barzahlen/last_check', time(), 'default', 0);
                Mage::getConfig()->cleanCache();
                $barzahlen = Mage::getSingleton('barzahlen/barzahlen');
                $transArray['shop_id'] = $barzahlen->getConfigData('shop_id');
                $transArray['shopsystem'] = 'Magento';
                $transArray['shopsystem_version'] = Mage::getVersion();
                $transArray['plugin_version'] = (string) Mage::getConfig()->getNode()->modules->ZerebroInternet_Barzahlen->version;
                $transArray['hash'] = $this->_createHash($transArray, $barzahlen->getConfigData('payment_key'));

                $currentVersion = $this->_requestVersion($transArray);
                if ($currentVersion != false) {
                    if ($currentVersion != $transArray['plugin_version']) {
                        Mage::getSingleton('core/session')->addNotice('Barzahlen-Update:' . Mage::helper('barzahlen')->__('bz_adm_now_available') . $currentVersion . Mage::helper('barzahlen')->__('bz_adm_over') . '<a href="' . Mage::helper("adminhtml")->getUrl('adminhtml/extension_local/index') . '">Magento Connect Manager</a>' . Mage::helper('barzahlen')->__('bz_adm_or') . '<a href="http://www.barzahlen.de/partner/integration/shopsysteme/2/magento">Barzahlen Homepage</a>.');
                    }
                }
            }
        }
    }

    /**
     * Requests the current version and parses the xml.
     *
     * @param array $transArray
     * @return boolean | string
     */
    protected function _requestVersion(array $transArray)
    {
        $xmlResponse = $this->_curlRequest($transArray);
        if ($xmlResponse === false) {
            return false;
        }

        if (!is_string($xmlResponse) || $xmlResponse == '') {
            Mage::helper('barzahlen')->bzLog('PluginCheck: No valid xml response received.');
            return false;
        }

        try {
            $xmlObj = new SimpleXMLElement($xmlResponse);
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog('PluginCheck: ' . $e);
            return false;
        }

        if ($xmlObj->{'result'} != 0) {
            Mage::helper('barzahlen')->bzLog('PluginCheck: XML response contains an error: ' . $xmlObj->{'error-message'});
            return false;
        }

        return $xmlObj->{'plugin-version'};
    }

    /**
     * Sends the cURL request.
     *
     * @param array $transArray
     * @return boolean | string
     */
    protected function _curlRequest(array $transArray)
    {
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://plugincheck.barzahlen.de/check');
            curl_setopt($curl, CURLOPT_POST, count($transArray));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $transArray);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, Mage::getRoot() . '/code/community/ZerebroInternet/Barzahlen/Model/Api/certs/ca-bundle.crt');
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, 1.1);
            $return = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error != '') {
                Mage::helper('barzahlen')->bzLog('PluginCheck: ' . $error);
            }

            return $return;
        } catch (Exception $e) {
            Mage::helper('barzahlen')->bzLog('PluginCheck: ' . $e);
            return false;
        }
    }
}
