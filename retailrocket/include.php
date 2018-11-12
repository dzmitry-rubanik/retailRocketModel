<?
\Bitrix\Main\Loader::IncludeModule("retailrocket");
global $DBType;

$arClasses=array(
    'RetailModelTable'=>'lib/retailmodel.php',
    'OnAfterOrderAddHandler'=>'lib/eventhandlers/onafterorderaddhandler.php',
);

\Bitrix\Main\Loader::registerAutoLoadClasses("retailrocket",$arClasses);
?>