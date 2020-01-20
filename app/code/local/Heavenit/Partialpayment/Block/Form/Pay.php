<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

class Heavenit_Partialpayment_Block_Form_Pay extends Mage_Payment_Block_Form
{

 	protected function _construct()
 	{
 		parent::_construct();
 		
		$this->setTemplate('partialpayment/form/pay.phtml');
 	}
	
}