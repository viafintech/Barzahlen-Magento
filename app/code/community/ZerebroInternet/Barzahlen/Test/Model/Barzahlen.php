<?php

class ZerebroInternet_Barzahlen_Test_Model_Barzahlen extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::getModel('barzahlen/barzahlen');
  }

  /**
   * Checks that all attributes are set.
   *
   * @test
   */
  public function testCorrectModuleSettingConstants() {

    //This test every Module Setting Constant as defined in the Barzahlen Model
    $this->assertAttributeEquals('barzahlen', '_code', $this->object);
    $this->assertAttributeEquals('barzahlen', '_paymentMethod', $this->object);
    $this->assertAttributeEquals('barzahlen/form', '_formBlockType', $this->object);
    $this->assertAttributeEquals('barzahlen/info', '_infoBlockType', $this->object);
    $this->assertAttributeEquals(true, '_canRefund', $this->object);
    $this->assertAttributeEquals(true, '_canRefundInvoicePartial', $this->object);
    $this->assertAttributeEquals(true, '_canUseInternal', $this->object);
    $this->assertAttributeEquals(true, '_canUseCheckout', $this->object);
    $this->assertAttributeEquals(true, '_canUseForMultishipping', $this->object);
  }

  /**
  * Tests that redirect urls are given back correctly.
  *
  * @test
  */
  public function testGettingUrls() {
    $this->object->getOrderPlaceRedirectUrl();
    $this->object->getBarzahlenRedirectUrl();
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