<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

Class soft_retailrocket extends CModule{
	var	$MODULE_ID = 'retailrocket';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
    var $errors;

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("SOFT_RETAILROCKET_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("SOFT_RETAILROCKET_MODULE_DESC");

		$this->PARTNER_NAME = getMessage("SOFT_RETAILROCKET_PARTNER_NAME");
		$this->PARTNER_URI = getMessage("SOFT_RETAILROCKET_PARTNER_URI");

		$this->exclusionAdminFiles=array(
			'..',
			'.',
			'menu.php',
			'operation_description.php',
			'task_description.php'
		);
	}

	function InstallDB($arParams = array())
	{
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/retailrocket/install/db/install.sql");
        if (!$this->errors) {
            return true;
        } else{
            return $this->errors;
        }
    }

	function UnInstallDB($arParams = array())
	{
		\Bitrix\Main\Config\Option::delete($this->MODULE_ID);
        global $DB;
        $this->errors = false;
        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/retailrocket/install/db/uninstall.sql");
        if (!$this->errors) {
            return true;
        } else
            return $this->errors;

        return true;

	}

	function InstallEvents()
	{
		\Bitrix\Main\EventManager::getInstance()->registerEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, '\Soft\Retailrocket\EventHandlers\OnAfterOrderAddHandler', "addOrderToRetailRocketTable");
        return true;
	}

	function UnInstallEvents()
	{
		\Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler("sale", "OnSaleOrderSaved", $this->MODULE_ID, '\Soft\Retailrocket\EventHandlers\OnAfterOrderAddHandler', "addOrderToRetailRocketTable");
        return true;
	}

	function InstallFiles($arParams = array())
	{
		$path = $this->GetPath()."/install/components";

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path)){
			CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		}

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
			CopyDirFiles($this->GetPath()."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			if ($dir = opendir($path)){
				while (false !== $item = readdir($dir)){
					if (in_array($item, $this->exclusionAdminFiles))
						continue;
					file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
						'<'.'? require($_SERVER["DOCUMENT_ROOT"]."'.$this->GetPath(true).'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/files')){
			$this->copyArbitraryFiles();
		}

		return true;
	}

	function UnInstallFiles()
	{
		\Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/'.$this->MODULE_ID.'/');

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/admin')){
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].$this->GetPath().'/install/admin/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin');
			if ($dir = opendir($path)){
				while (false !== $item = readdir($dir)){
					if (in_array($item, $this->exclusionAdminFiles))
						continue;
					\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}

		if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath().'/install/files')){
			$this->deleteArbitraryFiles();
		}

		return true;
	}

    function InstallOptions()
    {
        $options = Array(
            array('orderAdd',  0.57),
            array('basketAdd', 0.35),
            array('productClick', 0.05),
            array('productView',  0.03),
            array('orderAdd_old', 0.43),
            array('basketAdd_old',0.26),
            array('productClick_old', 0.04),
            array('productView_old', 0.02),
            );
        foreach ($options as $arOption){
            $optionName = $arOption[0];
            $optionValue = $arOption[1];
            Option::set('retailrocket', $optionName, $optionValue);
        }
        return true;
    }

	function copyArbitraryFiles()
	{
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = $this->GetPath().'/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object){
			$destPath = $rootPath.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
			($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
		}
	}

	function deleteArbitraryFiles()
	{
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = $this->GetPath().'/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object){
			if (!$object->isDir()){
				$file = str_replace($localPath, $rootPath, $object->getPathName());
				\Bitrix\Main\IO\File::deleteFile($file);
			}
		}
	}


	function isVersionD7()
	{
		return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	function GetPath($notDocumentRoot = false)
	{
		if ($notDocumentRoot){
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}else{
			return dirname(__DIR__);
		}
	}

	function getSitesIdsArray()
	{
		$ids = Array();
		$rsSites = CSite::GetList($by = "sort", $order = "desc");
		while ($arSite = $rsSites->Fetch()){
			$ids[] = $arSite["LID"];
		}

		return $ids;
	}

	function DoInstall()
	{
		global $APPLICATION;
		if ($this->isVersionD7()){
			\Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallDB();
			$this->InstallOptions();
			$this->InstallEvents();
			$this->InstallFiles();
		}else{
			$APPLICATION->ThrowException(Loc::getMessage("SOFT_RETAILROCKET_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("SOFT_RETAILROCKET_INSTALL"), $this->GetPath()."/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
        $this->UnInstallDB();

		\Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(Loc::getMessage("SOFT_RETAILROCKET_UNINSTALL"), $this->GetPath()."/install/unstep.php");
	}
}
?>