<?php
Class CMainRetailRocket
{
    /**
     * Добавлять (актуализировать) суммарный коэффициент для товара необходимо с периодичностью, например раз в 4 часа
    т.к. мгновенная актуализация создаст нагрузку и авто сброс кеша
     */
    public static function recalculateRating() {
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
        return 'CMainRetailRocket::recalculateRating();';
    }

    /**
     * Раз в сутки скрипт проверяет таблицу и уменьшать коэфф. для элементов больше 14 дней.
    Предлагаем реализовать автоматическую возможность чистки таблицы, иначе таблица разбухнет ужасно (например, по истечению 60 дней).
     */
    public static function calcCoefficient() {
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
        return 'CMainRetailRocket::calcCoefficient();';
    }
}