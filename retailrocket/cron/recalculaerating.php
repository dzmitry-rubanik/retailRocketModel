<?php
if (empty($_SERVER["DOCUMENT_ROOT"])){
	$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../../../");
	$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('retailrocket');
$propertyRatingCode = 'CML2_RATING';
$arPopularity = \Soft\Retailrocket\RetailModelTable::getList([
	'select' => ['product_id', 'sum'],
	'group' => ['product_id'],
	'runtime' => array(
		new \Bitrix\Main\Entity\ExpressionField('sum', 'ROUND(SUM(coefficient), 2)')
	)
])->fetchAll();
foreach ($arPopularity as $popularItem){
	\CIBlockElement::SetPropertyValuesEx($popularItem['product_id'], false, array($propertyRatingCode => $popularItem['sum']));
}