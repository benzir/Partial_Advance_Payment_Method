<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_Model_Allowpmethod {
	
    public function toOptionArray()
    {
        $_payments = Mage::getSingleton('payment/config')->getActiveMethods();
		$_methods = array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('–Please Select–')));
		
		$_exludePMethod = array("free", "partialpayment", "paypal_billing_agreement");
		foreach ($_payments as $_pmCode => $_pmModel) {
			if(!in_array($_pmCode, $_exludePMethod)){
				$_pmTitle = Mage::getStoreConfig('payment/'.$_pmCode.'/title');
				$_methods[] = array(
					'value' => $_pmCode,
					'label'   => $_pmTitle
				);
			}
		}
		
		return $_methods;
        		
    }
}
?>