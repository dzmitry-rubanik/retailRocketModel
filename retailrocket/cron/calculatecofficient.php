<?php
if (empty($_SERVER["DOCUMENT_ROOT"])){
	$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../../../../");
	$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('retailrocket');
$date = new \Bitrix\Main\Type\DateTime();
$date->add('-60 day');
$arPopularity = \Soft\Retailrocket\RetailModelTable::getList([
	'filter' => [
		'<=date' => $date,
	]
])->fetchAll();
foreach ($arPopularity as $item){
	\Soft\Retailrocket\RetailModelTable::delete($item['id']);
}

$date = new \Bitrix\Main\Type\DateTime();
$date->add('-14 day');
$arPopularity = \Soft\Retailrocket\RetailModelTable::getList([
	'filter' => [
		'<=date' => $date,
	]
])->fetchAll();

foreach ($arPopularity as $item){
	switch ($item['action']){
		case \Soft\Retailrocket\RetailModelTable::ORDER_ADD_EVENT : {
			$coef = \Bitrix\Main\Config\Option::get("retailrocket", \Soft\Retailrocket\RetailModelTable::ORDER_ADD_EVENT.'_old');
			\Soft\Retailrocket\RetailModelTable::update($item['id'], [
				'coefficient' => $coef
			]);
			break;
		}
		case \Soft\Retailrocket\RetailModelTable::BASKET_ADD_EVENT: {
			$coef = \Bitrix\Main\Config\Option::get("retailrocket", \Soft\Retailrocket\RetailModelTable::BASKET_ADD_EVENT.'_old');
			\Soft\Retailrocket\RetailModelTable::update($item['id'], [
				'coefficient' => $coef
			]);
			break;
		}
		case \Soft\Retailrocket\RetailModelTable::PRODUCT_CLICK_EVENT: {
			$coef = \Bitrix\Main\Config\Option::get("retailrocket", \Soft\Retailrocket\RetailModelTable::PRODUCT_CLICK_EVENT.'_old');
			\Soft\Retailrocket\RetailModelTable::update($item['id'], [
				'coefficient' => $coef
			]);
			break;
		}
		case \Soft\Retailrocket\RetailModelTable::PRODUCT_VIEW_EVENT: {
			$coef = \Bitrix\Main\Config\Option::get("retailrocket", \Soft\Retailrocket\RetailModelTable::PRODUCT_VIEW_EVENT.'_old');
			\Soft\Retailrocket\RetailModelTable::update($item['id'], [
				'coefficient' => $coef
			]);
			break;
		}
	}
}