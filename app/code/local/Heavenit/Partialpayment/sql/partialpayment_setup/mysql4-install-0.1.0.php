<?php
/**
*	Author		: 	Heaven IT Solutions
*	Author Email:   info@heavenit.com
*	Website		: 	http://www.heavenit.com/
*/

/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

/**
 * Add 'ztorm' field for entities
 */

$entities = array('quote_payment', 'order_payment');

$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => true,
    'required' => false
);

foreach ($entities as $entity) {
	$installer->addAttribute($entity, 'pp_method', $options);
	$installer->addAttribute($entity, 'pp_amount', $options);
}

$installer->endSetup();