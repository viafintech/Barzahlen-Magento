<?php

class ZerebroInternet_Barzahlen_Test_Model_Api_Request_Payment extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::getModel('barzahlen/api_request_payment');
  }

  /**
   * Checks getter functions.
   *
   * @test
   */
  public function testGetVariables() {
    $this->assertEquals(null, $this->object->getTransactionId());
    $this->assertEquals(null, $this->object->getPaymentSlipLink());
    $this->assertEquals(null, $this->object->getExpirationNotice());
    $this->assertEquals(null, $this->object->getInfotext1());
    $this->assertEquals(null, $this->object->getInfotext2());
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