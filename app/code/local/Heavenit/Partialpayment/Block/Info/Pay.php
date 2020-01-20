<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_Block_Info_Pay extends Mage_Payment_Block_Info
{
	
	protected function _prepareSpecificInformation($transport = null)
	{
		if (null !== $this->_paymentSpecificInformation) {
			return $this->_paymentSpecificInformation;
		}
		
		//$info = $this->getInfoInstance();
		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);
				
		$ct = $info->getId();
		
		$_payMethodTitle = Mage::getStoreConfig('payment/'.$info->getPpMethod().'/title');
		
		$transport->addData(array(
			Mage::helper('partialpayment')->__('Method') => $_payMethodTitle,
			Mage::helper('partialpayment')->__('Amount') => Mage::helper('core')->currency($info->getPpAmount(), true, false)
		));
		
		return $transport;
	}	
}