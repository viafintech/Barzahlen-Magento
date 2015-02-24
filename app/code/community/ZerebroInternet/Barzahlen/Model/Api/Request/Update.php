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

class ZerebroInternet_Barzahlen_Model_Api_Request_Update extends ZerebroInternet_Barzahlen_Model_Api_Request_Abstract
{
    protected $_type = 'update'; //!< request type
    protected $_transactionId; //!< origin transaction id
    protected $_orderId; //!< order id
    protected $_xmlAttributes = array('transaction-id', 'result', 'hash'); //!< update xml content

    /**
     * Construtor to set variable request settings. Adjusted for Magento
     *
     * @param array $arguements array with settings
     */
    public function __construct(array $arguments)
    {
        $this->_transactionId = $arguments['transactionId'];
        $this->_orderId = $arguments['orderId'];
    }

    /**
     * Builds array for request.
     *
     * @param string $shopId merchants shop id
     * @param string $paymentKey merchants payment key
     * @param string $language langauge code (ISO 639-1)
     * @param array $customVar custom variables from merchant
     * @return array for update request
     */
    public function buildRequestArray($shopId, $paymentKey, $language)
    {
        $requestArray = array();
        $requestArray['shop_id'] = $shopId;
        $requestArray['transaction_id'] = $this->_transactionId;
        $requestArray['order_id'] = $this->_orderId;
        $requestArray['hash'] = $this->_createHash($requestArray, $paymentKey);

        $this->_removeEmptyValues($requestArray);
        return $requestArray;
    }

    /**
     * Returns transaction id from xml array.
     *
     * @return received transaction id
     */
    public function getTransactionId()
    {
        return $this->getXmlArray('transaction-id');
    }
}
