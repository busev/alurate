<?
IncludeModuleLangFile(__FILE__);
//$aaa = CModule::IncludeModule('alurate');
//$bbb = $APPLICATION->GetGroupRight("alurate");

if($APPLICATION->GetGroupRight("alurate")>"D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services", // поместим в раздел "Сервис"
		"sort"        => 100,                    // вес пункта меню
		"url"         => "alutech_exchange_rates_admin.php?lang=".LANGUAGE_ID,  // ссылка на пункте меню
		"text"        => GetMessage("EXCH_RATE_PRICE"),       // текст пункта меню
		"title"       => GetMessage("EXCH_RATE_PRICE_TITLE"), // текст всплывающей подсказки
		"icon"        => "sale_menu_icon_catalog", // малая иконка
		"page_icon"   => "iblock_menu_icon_sections", // большая иконка
		"items_id"    => "menu_aluchanrat",  // идентификатор ветви
		"items"       => array(),          // остальные уровни меню сформируем ниже.
	);
	return $aMenu;
}
return false;
?>
