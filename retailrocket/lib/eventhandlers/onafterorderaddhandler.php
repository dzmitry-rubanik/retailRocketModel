<?
namespace Soft\Retailrocket\EventHandlers;

use Bitrix\Main\Localization,
    Bitrix\Sale,
    Bitrix\Main\Loader,
    Soft\Retailrocket,
    Bitrix\Main\Type\DateTime,
    Bitrix\Main\Config\Option;
Localization\Loc::loadMessages(__FILE__);

class OnAfterOrderAddHandler{
    static public function addOrderToRetailRocketTable(\Bitrix\Main\Event $event, $VALUES, $IS_NEW){
        if($event->getParameter('IS_NEW')) // проверяем, новый заказ ли?
        {
            $order = $event->getParameter('ENTITY');
            $module_id = "retailrocket";
            if(Loader::IncludeModule($module_id)){
                Loader::includeModule("catalog");
                Loader::includeModule("iblock");
                $basket = $order->getBasket();
                $action = Retailrocket\RetailModelTable::ORDER_ADD_EVENT;
                $weight = floatval(Option::get($module_id, $action));
                if (!empty($weight)) {
                    foreach ($basket as $basketItem) {
                        $productId = $basketItem->getProductId();
                        $sku = \CCatalogSKU::GetProductInfo($productId);
                        if (!empty($sku)) {
                            $productId = $sku["ID"];
                        }
                        $productWeight = $weight * intval($basketItem->getQuantity());
                        if (!empty($productId) && !empty($action) && !empty($productWeight)) {
                            $quantity = intval($basketItem->getQuantity());
                            $i = 0;
                            while ($i < $quantity) {
                                $i++;
                                Retailrocket\RetailModelTable::Add([
                                    'product_id' => intval($productId),
                                    'date' => new DateTime(),
                                    'coefficient' => $weight,
                                    'action' => $action
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}