<?php
namespace Soft\Retailrocket;

use Bitrix\Main\Diag\Debug;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Type;
use \Bitrix\Main\Entity\UpdateResult;
use \Bitrix\Main\Entity\Event;
use \Bitrix\Main\Entity\EntityError;

class RetailModelTable extends Entity\DataManager
{
	const PRODUCT_CLICK_EVENT = 'productClick';
	const BASKET_ADD_EVENT = 'basketAdd';
	const PRODUCT_VIEW_EVENT = 'productView';
	const ORDER_ADD_EVENT = 'orderAdd';

    public static function getTableName() {
        return "retail_rocket_model";
    }

    public static function getMap() {
        return [
            // ID
            new Entity\IntegerField('id', [
                'primary' => true,
                'autocomplete' => true
            ]),

            // product_id
            new Entity\IntegerField('product_id', [
                'required' => true
            ]),

            // date
            new Entity\DatetimeField('date', [
                'required' => true
            ]),
            // coefficient
            new Entity\FloatField('coefficient', [
                'required' => true
            ]),

            // action
            new Entity\StringField('action', [
                'required' => true
            ]),
        ];
    }
}