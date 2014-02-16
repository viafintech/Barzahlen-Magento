<?php

class ZerebroInternet_Barzahlen_Test_Model_Api_Notification extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::getModel('barzahlen/api_notification');
  }

  /**
   * Checks getter functions.
   *
   * @test
   */
  public function testGetVariables() {
    $this->assertEquals(null, $this->object->getNotificationType());
    $this->assertEquals(null, $this->object->getState());
    $this->assertEquals(null, $this->object->getRefundTransactionId());
    $this->assertEquals(null, $this->object->getTransactionId());
    $this->assertEquals(null, $this->object->getOriginTransactionId());
    $this->assertEquals(null, $this->object->getShopId());
    $this->assertEquals(null, $this->object->getCustomerEmail());
    $this->assertEquals(null, $this->object->getAmount());
    $this->assertEquals(null, $this->object->getCurrency());
    $this->assertEquals(null, $this->object->getOrderId());
    $this->assertEquals(null, $this->object->getOriginOrderId());
    $this->assertEquals(null, $this->object->getCustomVar0());
    $this->assertEquals(null, $this->object->getCustomVar1());
    $this->assertEquals(null, $this->object->getCustomVar2());
    $this->assertEquals(array(null, null, null), $this->object->getCustomVar());
  }

  /**
   * Unset everything before the next test.
   */
  protected function tearDown() {

    unset($this->object);
    parent::tearDown();
  }
}
?>