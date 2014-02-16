<?php

class ZerebroInternet_Barzahlen_Test_Model_Adminexceptions_Shopid extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  protected function setUp() {
    parent::setUp();

    $this->object = new ZerebroInternet_Barzahlen_Model_Adminexceptions_Shopid;

    $this->object->setScope('default');
    $this->object->setScopeId(0);
    $this->object->setPath('payment/barzahlen/shop_id');
  }

  /**
   * Tests that former value keeps in datebase, when empty string is put in.
   *
   * @test
   * @loadFixture Shopid
   */
  public function testShopidBeforeSaveWithEmptyString() {

    $this->object->setValue('');
    $this->object->save();
    $this->assertEquals('1', $this->object->getValue());
  }

  /**
   * Checks that valid string is written to database.
   *
   * @test
   * @loadFixture Shopid
   */
  public function testShopidBeforeSaveWithRandomString() {

    $this->object->setValue('Bar zahlen');
    $this->object->save();
    $this->assertEquals('Bar zahlen', $this->object->getValue());
  }

  /**
   * Unset everything before the next test.
   */
  protected function tearDown() {

    $this->object->delete();
    unset($this->object);
    parent::tearDown();
  }
}
?>