<?
IncludeModuleLangFile(__FILE__);
//$aaa = CModule::IncludeModule('alurate');
//$bbb = $APPLICATION->GetGroupRight("alurate");

if($APPLICATION->GetGroupRight("alurate")>"D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services", // �������� � ������ "������"
		"sort"        => 100,                    // ��� ������ ����
		"url"         => "alutech_exchange_rates_admin.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
		"text"        => GetMessage("EXCH_RATE_PRICE"),       // ����� ������ ����
		"title"       => GetMessage("EXCH_RATE_PRICE_TITLE"), // ����� ����������� ���������
		"icon"        => "sale_menu_icon_catalog", // ����� ������
		"page_icon"   => "iblock_menu_icon_sections", // ������� ������
		"items_id"    => "menu_aluchanrat",  // ������������� �����
		"items"       => array(),          // ��������� ������ ���� ���������� ����.
	);
	return $aMenu;
}
return false;
?>
