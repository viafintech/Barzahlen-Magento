<?php

class ZerebroInternet_Barzahlen_Test_Block_Info extends EcomDev_PHPUnit_Test_Case_Controller {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests that info block is created correctly.
   *
   * @test
   */
  public function testInfoBlock() {
    require_once("app/code/community/ZerebroInternet/Barzahlen/Block/Info.php");
    $this->object = new ZerebroInternet_Barzahlen_Block_Info;
    //$this->assertLayoutBlockCreated('payment_info');
  }

  /**
   * Unset everything before the next test.
   */
  public function tearDown() {

    unset($this->object);
    parent::tearDown();
  }
}