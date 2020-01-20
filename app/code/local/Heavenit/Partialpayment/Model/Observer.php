<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com
*/

class Heavenit_Partialpayment_Model_Observer
{ 	
	/*
	* Invoice Paid Due Adjustment
	*/
	public function invoicePaidDueAdjustment($observer) {

		/*
		 * Get Order & Invoice Data From Observer Event
		 * */
		$_event = $observer->getEvent();
		$_invoice = $_event->getInvoice();
		$_order = $_invoice->getOrder();
		$updatedTotalDue = $_order->getTotalPaid();
		//$updatedGrandTotal = $_order->getGrandTotal();

		/**
		 * Get Old Total Due and Grand Total
		 **/
		$orderId = $_order->getId();
		$_connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$totalPaidQuery = "SELECT `total_paid`, `grand_total` FROM `sales_flat_order` WHERE `entity_id` = '$orderId' ";
		$_resultData = $_connection->fetchAll($totalPaidQuery);
		$currentTotalDue = floatval($_resultData[0]['total_paid']);
		$currentGrandTotal = floatval($_resultData[0]['grand_total']);

		/*
		 * Here things work like following way
		 * if payment total due grater then zero then due will work (for all type of method)
		 * if payment method is not prepaid method due
		 * */
		$paymentMethod = $_order->getPayment()->getMethod();
		$allPaidPaymentMethod = array(
			"sslcommerz"/*, "pay"*/
		);
		
		if($currentTotalDue > 0 || (!in_array($paymentMethod, $allPaidPaymentMethod))) {
			$updatedTotalDue = floatval($updatedTotalDue);
			if($updatedTotalDue > $currentTotalDue) {
				$_order->setTotalPaid($currentTotalDue);
				$_order->save();
			}
		}
	}
}