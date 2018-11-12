<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Application,
    Soft\Retailrocket,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option;

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$id = $request->getPost("id");
$action = $request->getPost("action");
$module_id = "soft.retailrocket";
if(!empty($action) && !empty($id) && Loader::IncludeModule("soft.retailrocket")){
    Loader::includeModule("catalog");
    Loader::includeModule("iblock");
    $sku = \CCatalogSKU::GetProductInfo($id);
    if(!empty($sku)){
        $id = $sku["ID"];
    }
    $weigth = Option::get($module_id, $action);
    if (!empty($id) && !empty($weigth) && !empty($action)){
        $add = Retailrocket\RetailModelTable::Add([
            'product_id' => intval($id),
            'date'=> new DateTime(),
            'coefficient'=> floatval($weigth),
            'action'=> $action
        ]);
    }
}


