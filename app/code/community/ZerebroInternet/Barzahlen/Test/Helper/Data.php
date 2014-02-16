<?php

class ZerebroInternet_Barzahlen_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::helper('barzahlen');
  }

  /**
   * Checks that logging works.
   *
   * @test
   */
  public function testLogging() {
	$this->object->bzLog('An error occurred.');
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