<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PP_PERCENTAGE = 'payment/partialpayment/pp_percentage';
	
	const XML_ALLOW_METHODS = 'payment/partialpayment/allow_methods';
	
	const XML_TEST_MODE = 'payment/partialpayment/test_mode';
	
	const XML_DEBUG_MODE = 'payment/partialpayment/debug_mode';
	
 	public function getPpPercentage($store = null)
    {
        $pp_percentage = Mage::getStoreConfig(self::XML_PP_PERCENTAGE, $store);
        return $pp_percentage;
    }
 
 	public function getAllowPMethods($store = null)
    {
        $months = Mage::getStoreConfig(self::XML_ALLOW_METHODS, $store);
        $months = explode(',', $months);
        return $months;
    }
        
    public function getTestMode($store = null)
    {
        $flag = Mage::getStoreConfig(self::XML_TEST_MODE, $store);
        return $flag;
    }
    
    public function getDebugMode($store = null)
    {
        $flag = Mage::getStoreConfig(self::XML_DEBUG_MODE, $store);
        return $flag;
    }

	# SSLCOMMERZ IPN FUNCTION
	public function sslcommerzClient($url_ipn_listener="", $post_data="")
    {
		if($url_ipn_listener!="") {
			if($post_data!="") {
				$handle = curl_init();
				curl_setopt($handle, CURLOPT_URL, $url_ipn_listener);
				curl_setopt($handle, CURLOPT_POST, 1);
				curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
				curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
				$content = curl_exec($handle);
				$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	
				if($code == 200 && !(curl_errno($handle))){
					curl_close($handle);
					return $content;
				}else{
					curl_close($handle);
					return "FAILED TO CONNECT WITH SSLCOMMERZ API";
				}
			}else{
				return "NO POST DATA FOUND";
			}
		} else {
			return "SSLCOMMERZ API MISSING";
		}
    }

    
    public function addPartialAmountComment($_orderId, $_downPaymentAmount)
    {
        $_order = Mage::getModel('sales/order')->load($_orderId);
		
		$_ppMethodCode = $_order->getPayment()->getPpMethod();
		$_pmTitle = Mage::getStoreConfig('payment/'.$_ppMethodCode.'/title');
		
		$_downPaymentAmountFormat = strip_tags(Mage::helper('core')->currency($_downPaymentAmount,true,true));
		
		if($_order->getTotalPaid() == 0){
			// Add the comment and save the order (last parameter will determine if comment will be sent to customer)
			$_orderComment = "<strong>{$_downPaymentAmountFormat}</strong> partial amount has been paid by <strong>{$_pmTitle}</strong> payment method.";
			$_order->addStatusHistoryComment($_orderComment);
			
			$_order->setBaseTotalPaid($_downPaymentAmount);
			$_order->setTotalPaid($_downPaymentAmount);
			$_order->save();
		}else{
			// Add the comment and save the order (last parameter will determine if comment will be sent to customer)
			$_orderComment = "<strong>{$_downPaymentAmountFormat}</strong> due amount has been paid by <strong>{$_pmTitle}</strong> payment method.";
			$_order->addStatusHistoryComment($_orderComment);
			
			$_updatePaidAmount = $_order->getTotalPaid()+$_downPaymentAmount;
			$_updateDueAmount = $_order->getTotalDue()-$_downPaymentAmount;
						
			// Paid
			$_order->setBaseTotalPaid($_updatePaidAmount);
			$_order->setTotalPaid($_updatePaidAmount);
			// Due
			$_order->setBaseTotalDue($_updatePaidAmount);
			$_order->setTotalDue($_updatePaidAmount);
			$_order->save();
		}
    }
}