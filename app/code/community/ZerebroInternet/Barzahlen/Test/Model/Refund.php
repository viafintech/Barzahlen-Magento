<?php

class ObserverMock {

  public $order_id; //!< fixture order id

  /**
   * Constructer sets fixture order id for mock observer.
   *
   * @param integer $order_id fixture order id
   */
  public function __construct($order_id) {
    $this->order_id = $order_id;
  }

  /**
   * Mocks getEvent().
   */
  public function getEvent() {
    return $this;
  }

  /**
   * Mocks getcreditmemo().
   */
  public function getcreditmemo() {
    $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
                  ->addAttributeToFilter('order_id', array('eq' => $this->order_id))
                  ->addAttributeToSelect('*');
    foreach ($collection as $request) {
      $creditmemo = $request;
    }
    return $creditmemo;
  }
}

class ZerebroInternet_Barzahlen_Test_Model_Refund extends EcomDev_PHPUnit_Test_Case {

  protected $_apiSettings = array(array('shopId' => '1', 'paymentKey' => 'da49244dda9da8158f94134ba26a3ed258bde622', 'sandbox' => '0'));

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::getModel('barzahlen/refund');
  }

  /**
   * Tests that the credit memo is registered correctly with valid data.
   *
   * @test
   * @loadFixture order
   */
  public function testRefundObserverWithValidData() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<origin-transaction-id>1</origin-transaction-id>'.
             '<refund-transaction-id>1</refund-transaction-id>'.
             '<result>0</result>'.
             '<hash>34908b584bab00e6b21c642b41ef33ba27dc0e16617f451f82d85a6ea69f81838ee45c8db64fa56a0272c0dd937db021a80265eb6a1116e6e09f6f60aecc09c5</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $observer = new ObserverMock(1);
    $this->object->refundObserver($observer);
    $this->assertEquals(1, $observer->getcreditmemo()->getState());
    $this->assertEquals(1, $observer->getcreditmemo()->getTransactionId());
  }

  /**
   * Tests that no creditmemo is registered when invalid data are received.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testRefundObserverWithInvalidData() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<origin-transaction-id>2</origin-transaction-id>'.
             '<refund-transaction-id>1</refund-transaction-id>'.
             '<result>0</result>'.
             '<hash>4f0b32f7fe19b050307196ec1cd8c014570a3756915b9fd366ea5f7a5fc55190c812c9a4ee1dcebac2dc372eb0090a6cfc2150ed794678d291c83b56aa05ac91</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $observer = new ObserverMock(1);
    $this->object->refundObserver($observer);
    $this->assertEquals(null, $observer->getcreditmemo()->getState());
    $this->assertEquals(null, $observer->getcreditmemo()->getTransactionId());
  }

  /**
   * Tests that no credit memo is registered when there's a corrupt hash.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testRefundObserverWithInvalidHash() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<origin-transaction-id>1</origin-transaction-id>'.
             '<refund-transaction-id>1</refund-transaction-id>'.
             '<result>0</result>'.
             '<hash>34908b584bab00e6b21c642b41ef33ba27dc0e16617f451f82d85a6ea69f81838ee45c8db64fa56a0272c0dd937db021a80265eb6a1116e6e09f6f60aecc09c6</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $observer = new ObserverMock(1);
    $this->object->refundObserver($observer);
    $this->assertEquals(null, $observer->getcreditmemo()->getState());
    $this->assertEquals(null, $observer->getcreditmemo()->getTransactionId());
  }

  /**
   * Tests that no creditmemo is registered when an error xml is received.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testRefundObserverWithErrorXml() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<origin-transaction-id>1</origin-transaction-id>'.
             '<refund-transaction-id>1</refund-transaction-id>'.
             '<result>0</result>'.
             '<hash>34908b584bab00e6b21c642b41ef33ba27dc0e16617f451f82d85a6ea69f81838ee45c8db64fa56a0272c0dd937db021a80265eb6a1116e6e09f6f60aecc09c5</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'));
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $observer = new ObserverMock(1);
    $this->object->refundObserver($observer);
    $this->assertEquals(null, $observer->getcreditmemo()->getState());
    $this->assertEquals(null, $observer->getcreditmemo()->getTransactionId());
  }

  /**
   * Tests that no creditmemo is registered when an empty xml is received.
   *
   * @test
   * @loadFixture order
   * @expectedException Mage_Core_Exception
   */
  public function testRefundObserverWithEmptyXml() {

    $xml = '';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'));
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $observer = new ObserverMock(1);
    $this->object->refundObserver($observer);
    $this->assertEquals(null, $observer->getcreditmemo()->getState());
    $this->assertEquals(null, $observer->getcreditmemo()->getTransactionId());
  }

  /**
   * Checks that the creditmemo process is abort when another payment method than Bar zahlen was choosen.
   *
   * @test
   * @loadFixture order_paypal
   */
  public function testRefundObserverWithOtherPaymentMethod() {

    $observer = new ObserverMock(2);
    $this->object->refundObserver($observer);
    $this->assertEquals(null, $observer->getcreditmemo()->getState());
    $this->assertEquals(null, $observer->getcreditmemo()->getTransactionId());
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