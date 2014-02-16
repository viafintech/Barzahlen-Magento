<?php

class ZerebroInternet_Barzahlen_Test_Model_Adminexceptions_Title extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  protected function setUp() {
    parent::setUp();

    $this->object = new ZerebroInternet_Barzahlen_Model_Adminexceptions_Title;

    $this->object->setScope('default');
    $this->object->setScopeId(0);
    $this->object->setPath('payment/barzahlen/title');
  }

  /**
   * Tests that former value keeps in datebase, when empty string is put in.
   *
   * @test
   * @loadFixture Title
   */
  public function testTitleBeforeSaveWithEmptyString() {

    $this->object->setValue('');
    $this->object->save();
    $this->assertEquals('Barzahlen', $this->object->getValue());
  }

  /**
   * Checks that given string is written to the database.
   *
   * @test
   * @loadFixture Title
   */
  public function testTitleBeforeSaveWithValidString() {

    $this->object->setValue('Barzahlen (Pay Cash Online)');
    $this->object->save();
    $this->assertEquals('Barzahlen (Pay Cash Online)', $this->object->getValue());
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