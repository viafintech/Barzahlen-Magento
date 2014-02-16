<?php

class ZerebroInternet_Barzahlen_Test_Model_Adminexceptions_Specificcountry extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  protected function setUp() {
    parent::setUp();

    $this->object = new ZerebroInternet_Barzahlen_Model_Adminexceptions_Specificcountry;

    $this->object->setScope('default');
    $this->object->setScopeId(0);
    $this->object->setPath('payment/barzahlen/specificcountry');
  }

  /**
   * Tests that former value keeps in datebase, when empty array is put in.
   *
   * @test
   * @loadFixture Specificcountry
   */
  public function testSpecificcountryBeforeSaveWithEmptyString() {

    $this->object->setValue(array());
    $this->object->save();
    $this->assertEquals('DE', $this->object->getValue());
  }

  /**
   * Tests that former value keeps in datebase, when invalid array is put in.
   *
   * @test
   * @loadFixture Specificcountry
   */
  public function testSpecificcountryBeforeSaveWithNotAllowedCountries() {

    $this->object->setValue(array('DE','US'));
    $this->object->save();
    $this->assertEquals('DE', $this->object->getValue());
  }

  /**
   * Checks that valid input is written to database.
   *
   * @test
   * @loadFixture Specificcountry
   */
  public function testSpecificcountryBeforeSaveWithValidCountry() {

    $this->object->setValue(array('DE'));
    $this->object->save();
    $this->assertEquals('DE', $this->object->getValue());
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