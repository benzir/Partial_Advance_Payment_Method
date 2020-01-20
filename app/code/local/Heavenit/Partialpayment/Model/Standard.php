<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_Model_Standard extends Mage_Payment_Model_Method_Abstract {

	protected $_code = 'partialpayment';
	
	protected $_infoBlockType = 'partialpayment/info_pay';
	protected $_formBlockType = 'partialpayment/form_pay';
	
	public function assignData($data)
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		$info = $this->getInfoInstance();
		$info->setPpMethod($data->getPpMethod());
		$info->setPpAmount($data->getPpAmount());
		
		return $this;
	}
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('partialpayment/payment/redirect', array('_secure' => true));
	}
}
?>