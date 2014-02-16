<?php

class ZerebroInternet_Barzahlen_Test_Model_Adminexceptions_Notificationkey extends EcomDev_PHPUnit_Test_Case {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  protected function setUp() {
    parent::setUp();

    $this->object = new ZerebroInternet_Barzahlen_Model_Adminexceptions_Notificationkey;

    $this->object->setScope('default');
    $this->object->setScopeId(0);
    $this->object->setPath('payment/barzahlen/notification_key');
  }

  /**
   * Tests that former value keeps in datebase, when empty string is put in.
   *
   * @test
   * @loadFixture Notificationkey
   */
  public function testNotificationkeyBeforeSaveWithEmptyString() {

    $this->object->setValue('');
    $this->object->save();
    $this->assertEquals('3a25aea969b8768b034e81a64c4812136deab059', $this->object->getValue());
  }

  /**
   * Checks that valid string is written to database.
   *
   * @test
   * @loadFixture Notificationkey
   */
  public function testNotificationkeyBeforeSaveWithRandomString() {

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