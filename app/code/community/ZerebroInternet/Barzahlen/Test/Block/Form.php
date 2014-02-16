<?php

class ZerebroInternet_Barzahlen_Test_Block_Form extends EcomDev_PHPUnit_Test_Case_Controller {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests that form block is created correctly.
   *
   * @test
   */
  public function testFormBlock() {
    require_once("app/code/community/ZerebroInternet/Barzahlen/Block/Form.php");
    $this->object = new ZerebroInternet_Barzahlen_Block_Form;
    //$this->assertLayoutBlockCreated('payment_form');
  }

  /**
   * Unset everything before the next test.
   */
  public function tearDown() {

    unset($this->object);
    parent::tearDown();
  }
}