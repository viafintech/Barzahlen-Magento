<?php

require_once("app/code/community/ZerebroInternet/Barzahlen/controllers/CheckoutController.php");

class ZerebroInternet_Barzahlen_CheckoutController_Mock extends ZerebroInternet_Barzahlen_CheckoutController {

  public function loadLayout() {
    // do nothing
  }

  public function renderLayout() {
    // do nothing
  }
}

class ZerebroInternet_Barzahlen_Test_Controller_CheckoutController extends EcomDev_PHPUnit_Test_Case_Controller {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
    $this->object = new ZerebroInternet_Barzahlen_CheckoutController_Mock;
  }

  /**
   * Checks processing action with valid transaction information.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testProcessingActionWithValidTransacion() {

    // mock session
    $session = $this->getModelMock('checkout/session');
    $session->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(1));
    $session->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($session));
    $this->replaceByMock('singleton','checkout/session',$session);

    // mock order
    $order = $this->getModelMock('sales/order', array('loadByIncrementId', 'getPayment', 'getAdditionalInformation', 'getMethodInstance', 'getCode', 'sendNewOrderEmail', 'addStatusHistoryComment'));
    $order->expects($this->any())
          ->method('loadByIncrementId')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getCode')
          ->will($this->returnValue('barzahlen'));
    $order->expects($this->any())
          ->method($this->anything())
          ->will($this->returnValue($order));
    $this->replaceByMock('model','sales/order',$order);

    // mock request
    $answerArray = array(200,
                         '<?xml version="1.0" encoding="UTF-8"?>
                          <response>
                            <transaction-id>227840174</transaction-id>
                            <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
                            <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
                            <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
                            <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
                            <result>0</result>
                            <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
                          </response>');

    $request = $this->getModelMock('barzahlen/request');
    $request->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($answerArray));
    $this->replaceByMock('singleton','barzahlen/request',$request);


    $this->object->processingAction();
  }

  /**
   * Checks processing action with invalid transaction information.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testProcessingActionWithInvalidTransacion() {

    // mock session
    $session = $this->getModelMock('checkout/session');
    $session->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(1));
    $session->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($session));
    $this->replaceByMock('singleton','checkout/session',$session);

    // mock order
    $order = $this->getModelMock('sales/order', array('loadByIncrementId', 'getPayment', 'getAdditionalInformation', 'getMethodInstance', 'getCode', 'sendNewOrderEmail', 'addStatusHistoryComment'));
    $order->expects($this->any())
          ->method('loadByIncrementId')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getCode')
          ->will($this->returnValue('barzahlen'));
    $order->expects($this->any())
          ->method($this->anything())
          ->will($this->returnValue($order));
    $this->replaceByMock('model','sales/order',$order);

    // mock request
    $answerArray = array(400,
                         "<?xml version='1.0' encoding='UTF-8'?>".
                         "<response>".
                           "<result>10</result>".
                           "<error-message>shop id not found</error-message>".
                         "</response>");

    $request = $this->getModelMock('barzahlen/request');
    $request->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($answerArray));
    $this->replaceByMock('singleton','barzahlen/request',$request);


    $this->object->processingAction();
  }

  /**
   * Checks processing action without an order id.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testProcessingActionWithoutOrderId() {

    // mock session
    $session = $this->getModelMock('checkout/session', array('getLastRealOrderId'));
    $session->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(''));
    $session->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($session));
    $this->replaceByMock('singleton','checkout/session',$session);

    // mock order
    $order = $this->getModelMock('sales/order', array('loadByIncrementId', 'getPayment', 'getAdditionalInformation', 'getMethodInstance', 'getCode', 'sendNewOrderEmail', 'addStatusHistoryComment'));
    $order->expects($this->any())
          ->method('loadByIncrementId')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getCode')
          ->will($this->returnValue('barzahlen'));
    $order->expects($this->any())
          ->method($this->anything())
          ->will($this->returnValue($order));
    $this->replaceByMock('model','sales/order',$order);

    // mock request
    $answerArray = array(200,
                         '<?xml version="1.0" encoding="UTF-8"?>
                          <response>
                            <transaction-id>227840174</transaction-id>
                            <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
                            <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
                            <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
                            <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
                            <result>0</result>
                            <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
                          </response>');

    $request = $this->getModelMock('barzahlen/request');
    $request->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($answerArray));
    $this->replaceByMock('singleton','barzahlen/request',$request);


    $this->object->processingAction();
  }

  /**
   * Checks processing action with already sent transaction.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testProcessingActionWithDoneTransacion() {

    // mock session
    $session = $this->getModelMock('checkout/session');
    $session->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(1));
    $session->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($session));
    $this->replaceByMock('singleton','checkout/session',$session);

    // mock order
    $order = $this->getModelMock('sales/order', array('loadByIncrementId', 'getPayment', 'getAdditionalInformation', 'getMethodInstance', 'getCode', 'sendNewOrderEmail', 'addStatusHistoryComment'));
    $order->expects($this->any())
          ->method('loadByIncrementId')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getAdditionalInformation')
          ->will($this->returnValue('true'));
    $order->expects($this->any())
          ->method('getCode')
          ->will($this->returnValue('barzahlen'));
    $order->expects($this->any())
          ->method($this->anything())
          ->will($this->returnValue($order));
    $this->replaceByMock('model','sales/order',$order);

    // mock request
    $answerArray = array(200,
                         '<?xml version="1.0" encoding="UTF-8"?>
                          <response>
                            <transaction-id>227840174</transaction-id>
                            <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
                            <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
                            <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
                            <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
                            <result>0</result>
                            <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
                          </response>');

    $request = $this->getModelMock('barzahlen/request');
    $request->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($answerArray));
    $this->replaceByMock('singleton','barzahlen/request',$request);


    $this->object->processingAction();
  }

  /**
   * Checks processing action with an other payment method selected.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testProcessingActionWithOtherPaymentMethod() {

    // mock session
    $session = $this->getModelMock('checkout/session');
    $session->expects($this->any())
            ->method('getLastRealOrderId')
            ->will($this->returnValue(1));
    $session->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($session));
    $this->replaceByMock('singleton','checkout/session',$session);

    // mock order
    $order = $this->getModelMock('sales/order', array('loadByIncrementId', 'getPayment', 'getAdditionalInformation', 'getMethodInstance', 'getCode', 'sendNewOrderEmail', 'addStatusHistoryComment'));
    $order->expects($this->any())
          ->method('loadByIncrementId')
          ->will($this->returnValue($order));
    $order->expects($this->any())
          ->method('getCode')
          ->will($this->returnValue('paypal'));
    $order->expects($this->any())
          ->method($this->anything())
          ->will($this->returnValue($order));
    $this->replaceByMock('model','sales/order',$order);

    // mock request
    $answerArray = array(200,
                         '<?xml version="1.0" encoding="UTF-8"?>
                          <response>
                            <transaction-id>227840174</transaction-id>
                            <payment-slip-link>https://cdn.barzahlen.de/slip/227840174/c91dc292bdb8f0ba1a83c738119ef13e652a43b8a8f261cf93d3bfbf233d7da2.pdf</payment-slip-link>
                            <expiration-notice>Der Zahlschein ist 10 Tage gueltig.</expiration-notice>
                            <infotext-1><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-1>
                            <infotext-2><![CDATA[Text mit einem <a href="https://www.barzahlen.de" target="_blank">Link</a>]]></infotext-2>
                            <result>0</result>
                            <hash>903f45449ecadd1ef453df13d5d602f65d76bb9b81f213525ec2788f8f00eca2d6641e8b7441aa925a343fbd25cf578ab558a641216f8387d037688ac0dc0e84</hash>
                          </response>');

    $request = $this->getModelMock('barzahlen/request');
    $request->expects($this->any())
            ->method('sendRequest')
            ->will($this->returnValue($answerArray));
    $this->replaceByMock('singleton','barzahlen/request',$request);


    $this->object->processingAction();
  }

  /**
   * Checks that exceptions doesn't lead to an abort.
   *
   * @test
   * @loadFixture CheckoutController
   */
  public function testCheckoutExceptionHandling() {

    $create = $this->getModelMock('barzahlen/payment', array('getTransactionId'));
    $create->expects($this->once())
           ->method('getTransactionId')
           ->will($this->throwException(new Mage_Core_Exception('An error occurred.')));
    $this->replaceByMock('singleton','barzahlen/payment',$create);

    $this->object = new ZerebroInternet_Barzahlen_CheckoutController_Mock;
    $this->object->processingAction();
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