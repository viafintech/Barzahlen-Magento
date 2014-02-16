<?php

require_once("app/code/community/ZerebroInternet/Barzahlen/controllers/ResendController.php");

class ZerebroInternet_Barzahlen_ResendController_Mock extends ZerebroInternet_Barzahlen_ResendController {

  protected $_request;
  public $redirectUrl;
  public $redirectArray;

  public function __construct() {
    $this->_request = $_GET;
  }

  public function getRequest() {
    return $this;
  }

  public function getParam($param) {
    return $this->_request[$param];
  }

  protected function _redirect($url, $array) {
    $this->redirectUrl = $url;
    $this->redirectArray = $array;
  }
}

class ZerebroInternet_Barzahlen_Test_Controller_ResendController extends EcomDev_PHPUnit_Test_Case_Controller {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Checks resend payment slip with valid resend.
   *
   * @test
   * @loadFixture new_order
   */
  public function testResendPaymentSlipWithValidResend() {

    $_GET = array('order_id' => '100000003');
    $this->object = new ZerebroInternet_Barzahlen_ResendController_Mock;

    // mock order
    $order = $this->getModelMock('sales/order', array('getPayment', 'getAdditionalInformation'));
    $order->expects($this->any())
          ->method('getPayment')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getAdditionalInformation')
          ->will($this->returnValue('100000003'));
    $this->replaceByMock('model','sales/order',$order);

    // mock resend
    $resend = $this->getModelMock('barzahlen/resend', array('resend'));
    $resend->expects($this->once())
           ->method('resend')
           ->will($this->returnValue(true));
    $this->replaceByMock('singleton','barzahlen/resend',$resend);

    $this->object->paymentAction();
    $this->assertEquals('adminhtml/sales_order/view', $this->object->redirectUrl);
    $this->assertEquals(array('order_id' => '100000003'), $this->object->redirectArray);
  }

  /**
   * Checks resend payment slip with invalid resend.
   *
   * @test
   * @loadFixture paid_order
   */
  public function testResendPaymentSlipWithInvalidResend() {

    $_GET = array('order_id' => '100000004');
    $this->object = new ZerebroInternet_Barzahlen_ResendController_Mock;

    // mock order
    $order = $this->getModelMock('sales/order', array('getPayment', 'getAdditionalInformation'));
    $order->expects($this->any())
          ->method('getPayment')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getAdditionalInformation')
          ->will($this->returnValue('100000004'));
    $this->replaceByMock('model','sales/order',$order);

    // mock resend
    $resend = $this->getModelMock('barzahlen/resend', array('resend'));
    $resend->expects($this->once())
           ->method('resend')
           ->will($this->returnValue(false));
    $this->replaceByMock('singleton','barzahlen/resend',$resend);

    $this->object->paymentAction();
    $this->assertEquals('adminhtml/sales_order/view', $this->object->redirectUrl);
    $this->assertEquals(array('order_id' => '100000004'), $this->object->redirectArray);
  }

  /**
   * Checks resend refund slip with valid resend.
   *
   * @test
   * @loadFixture refunded_order
   */
  public function testResendRefundSlipWithValidResend() {

    $_GET = array('creditmemo_id' => '100000005');
    $this->object = new ZerebroInternet_Barzahlen_ResendController_Mock;

    // mock creditmemo
    $cmemo = $this->getModelMock('sales/order_creditmemo', array('getTransactionId'));
    $cmemo->expects($this->any())
          ->method('getTransactionId')
          ->will($this->returnValue('100000005'));
    $this->replaceByMock('model','sales/order_creditmemo',$cmemo);

    // mock resend
    $resend = $this->getModelMock('barzahlen/resend', array('resend'));
    $resend->expects($this->once())
           ->method('resend')
           ->will($this->returnValue(true));
    $this->replaceByMock('singleton','barzahlen/resend',$resend);

    $this->object->refundAction();
    $this->assertEquals('adminhtml/sales_creditmemo/view', $this->object->redirectUrl);
    $this->assertEquals(array('creditmemo_id' => '100000005'), $this->object->redirectArray);
  }

  /**
   * Checks resend refund slip with invalid resend.
   *
   * @test
   * @loadFixture successful_refunded_order
   */
  public function testResendRefundSlipWithInvalidResend() {

    $_GET = array('creditmemo_id' => '100000005');
    $this->object = new ZerebroInternet_Barzahlen_ResendController_Mock;

    // mock creditmemo
    $cmemo = $this->getModelMock('sales/order_creditmemo', array('getTransactionId'));
    $cmemo->expects($this->any())
          ->method('getTransactionId')
          ->will($this->returnValue('100000005'));
    $this->replaceByMock('model','sales/order_creditmemo',$cmemo);

    // mock resend
    $resend = $this->getModelMock('barzahlen/resend', array('resend'));
    $resend->expects($this->once())
           ->method('resend')
           ->will($this->returnValue(false));
    $this->replaceByMock('singleton','barzahlen/resend',$resend);

    $this->object->refundAction();
    $this->assertEquals('adminhtml/sales_creditmemo/view', $this->object->redirectUrl);
    $this->assertEquals(array('creditmemo_id' => '100000005'), $this->object->redirectArray);
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