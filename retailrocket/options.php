<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = 'retailrocket';

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight($module_id) < "S"){
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

\Bitrix\Main\Loader::includeModule($module_id);

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();



$aTabs = array(
	Array(
		'DIV'     => 'OSNOVNOE',
		'TAB'     => Loc::getMessage('SOFT_RETAILROCKET_TAB_OSNOVNOE'),
		'OPTIONS' => Array(
			array('orderAdd', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_ZAKAZA_TITLE'), 0.57, array('text', 0)),
			array('basketAdd', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_DOBAVLENIYA_V_KORZINU_TITLE'), 0.35, array('text', 0)),
			array('productClick', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_KLIKA_TITLE'), 0.05, array('text', 0)),
			array('productView', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_PROSMOTRA_TITLE'), 0.03, array('text', 0)),
			array('orderAdd_old', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_ZAKAZA_OLD_TITLE'), 0.43, array('text', 0)),
			array('basketAdd_old', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_DOBAVLENIYA_V_KORZINU_OLD_TITLE'), 0.26, array('text', 0)),
			array('productClick_old', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_KLIKA_OLD_TITLE'), 0.04, array('text', 0)),
			array('productView_old', Loc::getMessage('SOFT_RETAILROCKET_OPTION_KOEFFICIENT_PROSMOTRA_OLD_TITLE'), 0.02, array('text', 0)),		),
	),

	array(
		"DIV"     => "rights",
		"TAB"     => Loc::getMessage("MAIN_TAB_RIGHTS"),
		"TITLE"   => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
		"OPTIONS" => Array()
	),
);
#Сохранение
if ($request->isPost() && $request['Apply'] && check_bitrix_sessid()){

	foreach ($aTabs as $aTab){
		foreach ($aTab['OPTIONS'] as $arOption){
			if (!is_array($arOption))
				continue;

			if ($arOption['note'])
				continue;


			$optionName = $arOption[0];

			$optionValue = $request->getPost($optionName);

			Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
		}
	}
}

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<? $tabControl->Begin(); ?>
<form method='post'
	  action='<? echo $APPLICATION->GetCurPage() ?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>'
	  name='soft_retailrocket_settings'>

	<? foreach ($aTabs as $aTab):
		if ($aTab['OPTIONS']):?>
			<? $tabControl->BeginNextTab(); ?>
			<? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>

		<? endif;
	endforeach; ?>

	<?
	$tabControl->BeginNextTab();

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");

	$tabControl->Buttons(); ?>

	<input type="submit" name="Apply" value="<? echo GetMessage('MAIN_SAVE') ?>">
	<input type="reset" name="reset" value="<? echo GetMessage('MAIN_RESET') ?>">
	<?=bitrix_sessid_post();?>
</form>
<? $tabControl->End(); ?>

