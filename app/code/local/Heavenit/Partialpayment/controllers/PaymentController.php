<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_PaymentController extends Mage_Core_Controller_Front_Action {
	
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'Partialpayment', array('template' => 'partialpayment/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if($this->getRequest()->isPost()) {
			
			/*
			/* Your gateway's code to make sure the reponse you
			/* just got is from the gatway and not from some weirdo.
			/* This generally has some checksum or other checks,
			/* and is provided by the gateway.
			/* For now, we assume that the gateway's response is valid
			*/
			
			$validated = true;
			$orderId = '123'; // Generally sent by gateway
			
			if($validated) {
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId($orderId);
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
				
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				
				$order->save();
			
				Mage::getSingleton('checkout/session')->unsQuoteId();
				
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
			}
			else {
				// There is a problem in the response we got
				$this->cancelAction();
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect('');
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
			}
        }
	}
	
	// The redirect action is triggered when someone due payment
	public function duepayAction() {
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'Partialpayment', array('template' => 'partialpayment/duepayment.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}
	
	// The redirect action is triggered when someone due payment
	public function payresponseAction() {
		// Get the id of the order just made.
		$_orgOrderId = Mage::app()->getRequest()->getParam('order_id');
		
		if($this->getRequest()->isPost()) {
			$order = Mage::getModel('sales/order')->load($_orgOrderId);
			$_duePaymentAmount = round($order->getTotalDue());
			
			if ($order->getPayment()->getMethod() == "partialpayment" && $order->getPayment()->getPpMethod() == "sslcommerz")
			{
				echo '<pre>';print_r($_POST);//die;
				$id = $_POST['id'];
				$val_id = $_POST['val_id'];
				$tran_id = $_POST['tran_id'];
				$currency_amount = $_POST['currency_amount'];
				$currency_type = $_POST['currency_type'];
				$card_type = $_POST['card_type'];
				$store_amount = $_POST['store_amount'];
		
				if($order->getPayment()->getMethod() == "sslcommerz"){
				$_testMode = Mage::helper('sslcommerz')->getTestMode();
				}elseif($order->getPayment()->getMethod() == "partialpayment" && $order->getPayment()->getPpMethod() == "sslcommerz"){
					$_testMode = Mage::helper('partialpayment')->getTestMode();
				}
				if($_testMode){
					$_SSLValidationGatewayUrl = "https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php";
					$store_ID = "";
					$storePasword = "";
				}else{
					$_SSLValidationGatewayUrl = "https://securepay.sslcommerz.com/validator/api/validationserverAPI.php";
					$store_ID = "";
					$storePasword = "";
				}
				
				//Status Updated start here
				#require_once 'app/Mage.php';
				#umask(0);
				#Mage::app('default');
				/*
					const STATE_NEW             = 'new';
					const STATE_PENDING_PAYMENT = 'pending_payment';
					const STATE_PROCESSING      = 'processing';
					const STATE_COMPLETE        = 'complete';
					const STATE_CLOSED          = 'closed';
					const STATE_CANCELED        = 'canceled';
					const STATE_HOLDED          = 'holded';
					const STATE_PAYMENT_REVIEW  = 'payment_review';
				 */
				 
				$_checkGatewayError = false;
				# CALL THE FUNCTION TO CHECK THE RESULT
				if($this->SSLCOMMERZHashVarify($storePasword)){
					$requested_url = ($_SSLValidationGatewayUrl."?val_id=".urlencode($val_id)."&store_id=".urlencode($store_ID)."&store_passwd=".urlencode($storePasword)."&format=json");
		
					$handle = curl_init();
					curl_setopt($handle, CURLOPT_URL, $requested_url);
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
					
					$response = curl_exec($handle);
					
					$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
					
					// Check SSL Gateway Error
					if($code != 200 && (curl_errno($handle)))
					{
						$_checkGatewayError = true;
					}
				}else{
					$_checkGatewayError = true;
				}
		
				// Check SSL Gateway Error
				if($_checkGatewayError)
				{
					// Display the error
					echo '<h2>Constructor error</h2>' . $err;
					// At this point, you know the call that follows will fail
					exit();
				}
				else
				{
					$res = json_decode($response);
					#var_dump($res); exit;
					// API validation data
					$currency_amount = $res->currency_amount;
					$currency_type = $res->currency_type;
					$card_type = $res->card_type;
					$orderId = $res->tran_id;
					//  here $res will get 'VALID' if the transaction is a valid one 
					if ($res->status == "VALID") {
						// Write your success actions here 
						if ($currency_amount != null && $tran_id != null && ((round($order->getBaseGrandTotal(), 2) == $currency_amount) || ($_duePaymentAmount == $currency_amount)) && $currency_type == $order->getOrderCurrencyCode()){
							#$order = Mage::getModel('sales/order')->loadByIncrementID($orderId);
							#$order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
							#$order->save();
							//echo "<br />Payment Complete";
			
							/*
							 * Checkout successfully done, now the send the SMS for SSL Sslcommerz payment
							 * */
							$paymentObj = $order->getPayment();
							$paymentMethodCode = $paymentObj->getMethod();
							
							$_cardType = ($res->card_type)?$res->card_type:$res->card_brand;
							
							if(round($order->getBaseGrandTotal(), 2) == $currency_amount){
								$paymentObj->setSslCardType($_cardType);
								$paymentObj->setCardBank($res->card_issuer);
								$paymentObj->setCardNumber($res->card_no);
								if($res->emi_instalment){
									$paymentObj->setCardBank($res->emi_instalment);
								}
							}elseif($_duePaymentAmount == $currency_amount){
								$paymentObj->setPpAmount($_duePaymentAmount);
								Mage::helper('partialpayment')->addPartialAmountComment($order->getId(), $_duePaymentAmount);
							}
							
							$paymentObj->save();
							
							# Redirect Code Here
							Mage::getSingleton('core/session', array('name'=>'frontend'))->addSuccess(Mage::helper('partialpayment')->__('Thanks!! Your due Payment has been successfully completed.'));
						}			
					}			
					else
					{
						// PAYMENT INVALID 
						/******  Take necessary steps required  *******/ 
						//echo '<h3> Payment Failed</h3>';
						$orderId = $tran_id;
						if($currency_amount == null && $tran_id != null){
							Mage::getSingleton('core/session', array('name'=>'frontend'))->addSuccess(Mage::helper('partialpayment')->__('An error occurred in the process of payment.'));
						}
						else
						{
							Mage::getSingleton('core/session', array('name'=>'frontend'))->addSuccess(Mage::helper('partialpayment')->__('An error occurred in the process of payment.'));
						}
					}
				}
			}
		}
		// Go to Order Details
		Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('sales/order/view/order_id/'.$_orgOrderId, array('_secure'=>true)));
	}
	
	
	# FUNCTION TO CHECK HASH VALUE
	protected function SSLCOMMERZHashVarify($store_passwd="") {
		
		if(isset($_POST) && isset($_POST['verify_sign']) && isset($_POST['verify_key'])) {
			# NEW ARRAY DECLARED TO TAKE VALUE OF ALL POST
			
			$pre_define_key = explode(',', $_POST['verify_key']);
	
			$new_data = array();
			if(!empty($pre_define_key )) {
				foreach($pre_define_key as $value) {
					if(isset($_POST[$value])) {
						$new_data[$value] = ($_POST[$value]);
					}
				}		
			}		
			# ADD MD5 OF STORE PASSWORD
			$new_data['store_passwd'] = md5($store_passwd);
			
			# SORT THE KEY AS BEFORE
			ksort($new_data);
			
			$hash_string="";
			foreach($new_data as $key=>$value) { $hash_string .= $key.'='.($value).'&'; }
			$hash_string = rtrim($hash_string,'&');	
			
			if(md5($hash_string) == $_POST['verify_sign']) {
			
				return true;
				
			} else {
			return false;
		}
		} else return false;
	}
}