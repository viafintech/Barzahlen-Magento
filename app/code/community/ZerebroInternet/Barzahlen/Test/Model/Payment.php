<?php

class ZerebroInternet_Barzahlen_Test_Model_Payment extends EcomDev_PHPUnit_Test_Case {

  protected $_apiSettings = array(array('shopId' => '1', 'paymentKey' => 'da49244dda9da8158f94134ba26a3ed258bde622', 'sandbox' => '0'));

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    Mage::getConfig()->reinit();
    Mage::app()->reinitStores();
    parent::setUp();
    Mage::getSingleton('checkout/session')->setLastRealOrderId('100000010');
    $this->object = Mage::getModel('barzahlen/payment');
    $this->session = Mage::getSingleton('checkout/session');
  }

  /**
   * Checks getTransactionId with a valid response.
   *
   * @test
   * @loadFixture order
   */
  public function testTransactionWithValidResponse() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <response>
              <transaction-id>227840174</transaction-id>
              <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
              <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
              <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
              <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
              <result>0</result>
              <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
            </response>';

    // mock request
    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->any())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $this->session->setLastRealOrderId('100000010');
    $this->object->getTransactionId();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('new', $order->getState());
  }

  /**
   * Checks getTransactionId with an invalid response.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testTransactionWithInvalidResponse() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<result>10</result>'.
             '<error-message>shop not found</error-message>'.
           '</response>';

    // mock request
    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->any())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $this->session->setLastRealOrderId('100000010');
    $this->object->getTransactionId();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('canceled', $order->getState());
  }

  /**
   * Checks getTransactionId with an invalid and a valid response.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testTransactionWithInvalidAndValidResponse() {

    $xml1 = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<response>'.
              '<result>10</result>'.
              '<error-message>shop not found</error-message>'.
            '</response>';

    $xml2 = '<?xml version="1.0" encoding="UTF-8"?>
             <response>
               <transaction-id>227840174</transaction-id>
               <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
               <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
               <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
               <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
               <result>0</result>
              <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
             </response>';

    // mock request
    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->any())
            ->method('_connectToApi')
            ->will($this->onConsecutiveCalls($xml1, $xml2));
    $this->replaceByMock('model','barzahlen/api',$request);

    $this->session->setLastRealOrderId('100000010');
    $this->object->getTransactionId();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('new', $order->getState());
  }

  /**
   * Checks getTransactionId with a non-existing order.
   *
   * @test
   * @loadFixture order
   */
  public function testTransactionWithNonExistingOrder() {

    $this->session->setLastRealOrderId('100000005');

    $this->assertEquals(null, $this->object->getTransactionId());

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('new', $order->getState());
  }

  /**
   * Checks getTransactionId with a canceled order.
   *
   * @test
   * @loadFixture order
   */
  public function testTransactionWithCanceledOrder() {

    $this->session->setLastRealOrderId('100000010');
    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $order->cancel()->save();

    $this->assertEquals(null, $this->object->getTransactionId());

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('canceled', $order->getState());
  }

  /**
   * Checks getTransactionId with a paid order.
   *
   * @test
   * @loadFixture order
   */
  public function testTransactionWithPaidOrder() {

    $this->session->setLastRealOrderId('100000010');
    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $order->getPayment()->setAdditionalInformation('transaction_id', '3')->save();

    $this->assertEquals(null, $this->object->getTransactionId());

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('new', $order->getState());
  }

  /**
   * Checks getTransactionId with another payment method.
   *
   * @test
   * @loadFixture order_paypal
   */
  public function testTransactionWithoutBarzahlenAsMethod() {

    $this->session->setLastRealOrderId('100000010');
    $this->object->getTransactionId();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000010');
    $this->assertEquals('new', $order->getState());
  }

  /**
   * Unset everything before the next test.
   */
  public function tearDown() {

    unset($this->object);
    parent::tearDown();
  }
}
?>