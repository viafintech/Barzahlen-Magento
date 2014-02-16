<?php

require_once("app/code/community/ZerebroInternet/Barzahlen/controllers/IpnController.php");

class ZerebroInternet_Barzahlen_IpnController_Mock extends ZerebroInternet_Barzahlen_IpnController {

  protected $_request;

  public function __construct() {
    $this->_request = $_GET;
  }

  public function getRequest() {
    return $this;
  }

  public function getQuery() {
    return $this->_request;
  }
}

class ZerebroInternet_Barzahlen_Test_Controller_IpnController extends EcomDev_PHPUnit_Test_Case_Controller {

  /**
   * Sets everything for a new test. parent::setUp() is necessary to enable fixtures.
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Checks that there are no data changes when a notifiction for a non-existing order is received.
   *
   * @test
   * @loadFixture new_order
   */
  public function testValidPaidAgainstNonExistingOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '3',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000006',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '0d24005c408d8ed76252b8c520cd231b10061031efed6d876975d22495ed67fc5acfe2ffc04171044a87f2151966f03571a8ec07259d82ec1b29acc0f43627ac'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('new', $order->getState());
    $this->assertEquals('pending', $order->getStatus());
  }

  /**
   * Checks that there are no state changes when the order isn't connected to the received transaction id.
   *
   * @test
   * @loadFixture new_order
   */
  public function testValidPaidAgainstNonExistingTransaction() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '6',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => 'c58c589dab3b33781aa472cb7c35fabc5c7e9d09550960b6f481402860e0b6b04232c5b6e76e0609d3cf77e76d29ccc03730052781f2378d84dd2fe9133022c2'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('new', $order->getState());
    $this->assertEquals('pending', $order->getStatus());
  }

  /**
   * Tests that a paid notification is fullfilled successful.
   *
   * @test
   * @loadFixture new_order
   */
  public function testValidPaidAgainstNewOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '3',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '6a227d559521476b024468c181923e9af7d146d14f435ecd9d02bb87c95f8b21f81dcd6128f717f894c927c820a6d6e8841f52005397924ea8bd23d76eda7274'
                 );

    $create = $this->getModelMock('sales/order_invoice_api', array('create'));
    $create->expects($this->once())
           ->method('create')
           ->will($this->returnCallback(array($this, 'invoiceHelper')));
    $this->replaceByMock('model','sales/order_invoice_api',$create);

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('processing', $order->getState());
    //$this->assertEquals('processing', $order->getStatus());
  }

  /**
   * Checks that a paid order can't be paid again.
   *
   * @test
   * @loadFixture paid_order
   */
  public function testValidPaidAgainstPaidOrder() {

    $_GET = array('state' => 'paid',
                  'transaction_id' => '4',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000004',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '326a2a0d56bb67d2c87a7daa32104c468d71725fad360051b028774989f78dac5a801a5e0458f32095fa6831967b637b40d2184f4ff10a18dccd3080f6fb0917'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000004');
    $this->assertEquals('processing', $order->getState());
    $this->assertEquals('processing', $order->getStatus());
  }

  /**
   * Tests that a notification isn't accepted when transaction id is no numeric value.
   *
   * @test
   * @loadFixture new_order
   */
  public function testInvalidPaidAgainstNewOrderGetFail() {

    $_GET = array('state' => 'expired',
                  'transaction-id' => '<hack>3</hack>',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '59030ebeb4d860ce52b4a7eb4ec39adebceffe3ccbab81ff7caccfbdbf47ea13f6bd5c66417f3cac50fff9f9ef7dac8d2515e87cea9d07df3145c6792c02017b'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('new', $order->getState());
    $this->assertEquals('pending', $order->getStatus());
  }

  /**
   * Checks that a notification with an invalid hash is declined.
   *
   * @test
   * @loadFixture new_order
   */
  public function testInvalidPaidAgainstNewOrderHashFail() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '3',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '59030ebeb4d860ce52b4a7eb4ec39adebceffe3ccbab81ff7caccfbdbf47ea13f6bd5c66417f3cac50fff9f9ef7dac8d2515e87cea9d07df3145c6792c02017b'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('new', $order->getState());
    $this->assertEquals('pending', $order->getStatus());
  }

  /**
   * Tests that undefined notification states are rejected.
   *
   * @test
   * @loadFixture new_order
   */
  public function testInvalidStateAgainstNewOrder() {

    $_GET = array('state' => 'paid_expired',
                  'transaction_id' => '3',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '4f4ca47bbc55cefe33aaf4a476cc87bad9288f923cd48c2c4b7083f2246d50fd4d99c823780db1c09763e8f088b6c5982522159a8a07ddce2f3fcb7d30861401'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('new', $order->getState());
    $this->assertEquals('pending', $order->getStatus());
  }

  /**
   * Checks that a valid expired notification is procceded correctly.
   *
   * @test
   * @loadFixture new_order
   */
  public function testValidExpiredAgainstNewOrder() {

    $_GET = array('state' => 'expired',
                  'transaction_id' => '3',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'order_id' => '100000003',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '5f903f1732e84e42169b92014066b2caf09669ae1cc445efcd79552d43640302756ebc84cf70745513217569ff45baacb952acf9be0e4418e99d55085d5b6090'
                 );

    $create = $this->getModelMock('sales/order', array('registerCancellation'));
    $create->expects($this->once())
           ->method('registerCancellation')
           ->will($this->returnCallback(array($this, 'cancelHelper')));
    $this->replaceByMock('model','sales/order',$create);

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $this->assertEquals('canceled', $order->getState());
    $this->assertEquals('canceled', $order->getStatus());
  }

  /**
   * Tests that a valid refund_completed notification is performed correctly.
   *
   * @test
   * @loadFixture refunded_order
   */
  public function testValidRefundCompletedAgainstPaidOrder() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '5',
                  'origin_transaction_id' => '5',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '100000005',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '8b9c0b9038a727fff303ce4c394e313a2d890da4cd1f2970456760887b6c1f11ba43ce43d1242d9adc3c1cf92633f1377b7a81832eb16e825977b5f1d2550d1b'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000005');
    $this->assertEquals('closed', $order->getState());
    $this->assertEquals('closed', $order->getStatus());

    $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
                  ->addAttributeToFilter('order_id', array('eq' => $order->getEntityId()))
                  ->addAttributeToSelect('*');
    foreach ($collection as $cm) {
      $creditmemo = $cm;
    }
    $this->assertEquals('2', $creditmemo->getState());
  }

  /**
   * Tests that a valid refund_expired notification is performed correctly.
   *
   * @test
   * @loadFixture refunded_order
   */
  public function testValidRefundExpiredAgainstPaidOrder() {

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '5',
                  'origin_transaction_id' => '5',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '100000005',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '7230f941a9eda8e44067015b8719382285e5ac1e6523d759d317642b4b655c735f9de0089d45db57a5ab330d6160624711307ed2d005db54f012e88c875f4873'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000005');
    $this->assertEquals('closed', $order->getState());
    $this->assertEquals('closed', $order->getstate());
    $this->assertEquals(null, $order->getTotalOfflineRefunded());
    $this->assertEquals(null, $order->getBaseTotalOfflineRefunded());
    $this->assertEquals(null, $order->getTotalOfflineRefunded());
    $this->assertEquals(null, $order->getBaseTotalOfflineRefunded());
    $this->assertEquals(null, $order->getBaseSubtotalRefunded());
    $this->assertEquals(null, $order->getSubtotalRefunded());
    $this->assertEquals(null, $order->getBaseTaxRefunded());
    $this->assertEquals(null, $order->getTaxRefunded());
    $this->assertEquals(null, $order->getBaseShippingRefunded());
    $this->assertEquals(null, $order->getShippingRefunded());

    $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
                  ->addAttributeToFilter('order_id', array('eq' => $order->getEntityId()))
                  ->addAttributeToSelect('*');
    foreach ($collection as $cm) {
      $creditmemo = $cm;
    }
    $this->assertEquals('3', $creditmemo->getState());
  }

  /**
   * Tests that a valid refund_expired notification fails at already completed refund.
   *
   * @test
   * @loadFixture successful_refunded_order
   */
  public function testValidRefundExpiredAgainstCompletedRefund() {

    $_GET = array('state' => 'refund_expired',
                  'refund_transaction_id' => '5',
                  'origin_transaction_id' => '5',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '100000005',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '7230f941a9eda8e44067015b8719382285e5ac1e6523d759d317642b4b655c735f9de0089d45db57a5ab330d6160624711307ed2d005db54f012e88c875f4873'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000005');
    $this->assertEquals('closed', $order->getState());
    $this->assertEquals('closed', $order->getstate());

    $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
                  ->addAttributeToFilter('order_id', array('eq' => $order->getEntityId()))
                  ->addAttributeToSelect('*');
    foreach ($collection as $cm) {
      $creditmemo = $cm;
    }
    $this->assertEquals('2', $creditmemo->getState());
  }

  /**
   * Checks that a refund notification with an invalid refund_transaction_id is rejected.
   *
   * @test
   * @loadFixture refunded_order
   */
  public function testValidRefundCompletedAgainstNonExistingRefundId() {

    $_GET = array('state' => 'refund_completed',
                  'refund_transaction_id' => '6',
                  'origin_transaction_id' => '5',
                  'shop_id' => '1',
                  'customer_email' => 'foo@bar.com',
                  'amount' => '499.95',
                  'currency' => 'EUR',
                  'origin_order_id' => '100000005',
                  'custom_var_0' => 'MainStore',
                  'custom_var_1' => 'Magento',
                  'custom_var_2' => 'Bar zahlen',
                  'hash' => '640a007a308b40abea98808a09e6e0aa66b9e20b93356719a2f777e9472122ace37846ba9c1c244587b7548dfb135da42e056c91784aec1f1ecae39d63d71b46'
                 );

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();

    $order = Mage::getModel('sales/order')->loadByIncrementId('100000005');
    $this->assertEquals('closed', $order->getState());
    $this->assertEquals('closed', $order->getStatus());

    $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
                  ->addAttributeToFilter('order_id', array('eq' => $order->getEntityId()))
                  ->addAttributeToSelect('*');
    foreach ($collection as $cm) {
      $creditmemo = $cm;
    }
    $this->assertEquals('1', $creditmemo->getState());
  }

  /**
   * Checks that exceptions doesn't lead to an abort.
   *
   * @test
   * @loadFixture new_order
   */
  public function testIpnExceptionHandling() {

    $create = $this->getModelMock('barzahlen/ipn', array('sendResponseHeader'));
    $create->expects($this->once())
           ->method('sendResponseHeader')
           ->will($this->throwException(new Mage_Core_Exception('An error occurred.')));
    $this->replaceByMock('model','barzahlen/ipn',$create);

    $this->object = new ZerebroInternet_Barzahlen_IpnController_Mock;
    $this->object->indexAction();
  }

  /**
   * Imitates invoice creation.
   */
  function invoiceHelper() {
    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $order->setState('processing', true)->save();
    return $order;
  }

  /**
   * Imitates order cancellation.
   */
  function cancelHelper() {
    $order = Mage::getModel('sales/order')->loadByIncrementId('100000003');
    $order->setState('canceled', 'canceled')->save();
    return $order;
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