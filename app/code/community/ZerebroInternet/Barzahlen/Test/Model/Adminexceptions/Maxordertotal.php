<?php

class ZerebroInternet_Barzahlen_Test_Model_Adminexceptions_Maxordertotal extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  protected function setUp() {
    parent::setUp();

    $this->object = new ZerebroInternet_Barzahlen_Model_Adminexceptions_Maxordertotal;

    $this->object->setScope('default');
    $this->object->setScopeId(0);
    $this->object->setPath('payment/barzahlen/max_order_total');
  }

  /**
   * Tests that default value is written to datebase, when too high integer value is put in.
   *
   * @test
   * @loadFixture Maxordertotal
   */
  public function testBeforeSaveForMaxordertotal() {

    $this->object->setValue('2500');
    $this->object->save();
    $this->assertEquals('1000', $this->object->getValue());
  }

  /**
   * Checks that valid integer value is written to database.
   *
   * @test
   * @loadFixture Maxordertotal
   */
  public function testBeforeSaveForMaxordertotal2() {

    $this->object->setValue('500');
    $this->object->save();
    $this->assertEquals('500', $this->object->getValue());
  }

  /**
   * Tests that default value is written to datebase, when too low integer value is put in.
   *
   * @test
   * @loadFixture Maxordertotal
   */
  public function testBeforeSaveForMaxordertotal3() {

    $this->object->setValue('-200');
    $this->object->save();
    $this->assertEquals('1000', $this->object->getValue());
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