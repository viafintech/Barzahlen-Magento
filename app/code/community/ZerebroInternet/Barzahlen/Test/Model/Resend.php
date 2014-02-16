<?php

class ZerebroInternet_Barzahlen_Test_Model_Resend extends EcomDev_PHPUnit_Test_Case {

  protected $_apiSettings = array(array('shopId' => '1', 'paymentKey' => 'da49244dda9da8158f94134ba26a3ed258bde622', 'sandbox' => '0'));

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = Mage::getModel('barzahlen/resend');
  }

  /**
   * Tests resend function with valid response.
   *
   * @test
   */
  public function testResendWithValidResponse() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<transaction-id>1</transaction-id>'.
             '<result>0</result>'.
             '<hash>f3c2814a4e98cf0c22a0099d8ea4670295a8739745000d40d0f7e8eeebaf23674a8a14ee907a9d65b0bee90d025fcf6dbebba8778ef25b231e394b4f315189cb</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $this->assertTrue($this->object->resend('1'));
  }

  /**
   * Tests resend function with invalid response.
   *
   * @test
   */
  public function testResendWithInvalidResponse() {

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
           '<response>'.
             '<transaction-id>1</transaction-id>'.
             '<result>0</result>'.
             '<hash>f3c2814a4e98cf0c22a0099d8ea4670295a8739745000d40d0f7e8eeebaf23674a8a14ee907a9d65b0bee90d025fcf6dbebba8778ef25b231e394b4f315189cc</hash>'.
           '</response>';

    $request = $this->getModelMock('barzahlen/api', array('_connectToApi'), false, $this->_apiSettings);
    $request->expects($this->once())
            ->method('_connectToApi')
            ->will($this->returnValue($xml));
    $this->replaceByMock('model','barzahlen/api',$request);

    $this->assertFalse($this->object->resend('1'));
  }

  /**
   * Unset everything before the next test.
   */
  public function tearDown() {

    unset($this->object);
    parent::tearDown();
  }
}
?>