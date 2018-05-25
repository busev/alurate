<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 26.10.2017
 * Time: 15:24
 */
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alurate/include.php"); // инициализация модуля

// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("alurate");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

// значения по умолчанию
$strPREFIX_PROPERTY = 'EXRT_PROPERTY_';
$arExchRatItems = array();
$arFindItems = array();

function __AddPropCellID($intOFPropID)
{
	return (0 < intval($intOFPropID) ? $intOFPropID : '');
}
function __AddPropCellName($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="50" maxlength="255" name="<?echo $strPrefix.$intOFPropID?>_NAME" id="<?echo $strPrefix.$intOFPropID?>_NAME" value="<?echo $arPropInfo['NAME']?>" onchange="translitName(this, '<?echo $strPrefix.$intOFPropID?>_CODE')"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}
function __AddPropCellEuro($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="7" maxlength="30" name="<?echo $strPrefix.$intOFPropID?>_EUR_RATE" id="<?echo $strPrefix.$intOFPropID?>_EUR_RATE" value="<?echo $arPropInfo['EUR_RATE']?>" oninput="convertEuro(this.value, ExchRatRes, '<?echo $strPrefix.$intOFPropID?>_BYN_RATE')" ><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}
function __AddPropCellByn($intOFPropID,$strPrefix,$arPropInfo,$kursEuro)
{
	ob_start();
	?><input type="text" size="7" maxlength="30" name="<?echo $strPrefix.$intOFPropID?>_BYN_RATE" id="<?echo $strPrefix.$intOFPropID?>_BYN_RATE" value="<?echo( ceil( $arPropInfo['EUR_RATE'] * $kursEuro ) ); ?>"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}
function __AddPropCellCode($intOFPropID,$strPrefix,$arPropInfo)
{
	ob_start();
	?><input type="text" size="20" maxlength="50" name="<?echo $strPrefix.$intOFPropID?>_CODE" id="<?echo $strPrefix.$intOFPropID?>_CODE" value="<?echo $arPropInfo['CODE']?>" onchange="errMessToCode(this.value, '<?echo $strPrefix.$intOFPropID?>_NAME', '<?echo $strPrefix.$intOFPropID?>_CODE')"><?
	$strResult = ob_get_contents();
	ob_end_clean();
	return $strResult;
}
function __AddPropCellDelete($intOFPropID,$strPrefix,$arPropInfo)
{
	$strResult = '&nbsp;';
	$strResult = '<input type="checkbox" name="'.$strPrefix.$intOFPropID.'_DEL" id="'.$strPrefix.$intOFPropID.'_DEL" value="Y">';
	return $strResult;
}
function __AddPropRow($intOFPropID,$strPrefix,$arPropInfo,$kursEuro)
{
	$strResult = '<tr id="'.$strPrefix.$intOFPropID.'">
	<td style="vertical-align:middle;">'.__AddPropCellID($intOFPropID).'</td>	
	<td>'.__AddPropCellName($intOFPropID,$strPrefix,$arPropInfo).'</td>	
	<td>EURO: '.__AddPropCellEuro($intOFPropID,$strPrefix,$arPropInfo).'</td>	
	<td>BYN: '.__AddPropCellByn($intOFPropID,$strPrefix,$arPropInfo,$kursEuro).'</td>
	<td>'.__AddPropCellCode($intOFPropID,$strPrefix,$arPropInfo).'</td>
	<td style="text-align: center; vertical-align:middle;">'.__AddPropCellDelete($intOFPropID,$strPrefix,$arPropInfo).'</td>
	</tr>';
	return $strResult;
}

// сформируем список закладок
$aTabs = array(
	array(
	    "DIV" => "edit1",
      "TAB" => GetMessage("EXCH_RATE_TAB1"),
      "ICON"=>"site_edit",
      "TITLE"=>GetMessage("EXCH_RATE_TAB1_T")
  ),
	array(
	    "DIV" => "edit2",
      "TAB" => GetMessage("EXCH_RATE_TAB2"),
      "ICON"=>"site_edit",
      "TITLE"=>GetMessage("EXCH_RATE_TAB2_T")
  ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$APPLICATION->AddHeadScript('/bitrix/js/alurate/alutech_exchange_rates_admin.js');



// ******************************************************************** //
//                ОБРАБОТКА ИЗМЕНЕНИЙ ФОРМЫ                             //
// ******************************************************************** //
if(
	$REQUEST_METHOD == "POST" // проверка метода вызова страницы
	&&
	($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
	&&
	$POST_RIGHT=="W"          // проверка наличия прав на запись для модуля
	&&
	check_bitrix_sessid()     // проверка идентификатора сессии
)
{
  // Переменная для пересчета перед сохранением в БД Евро в рубли
	$conversionRate = '';

  // Обновление курса Евро
	if($_POST['rateAlutecRadio'])
  {
	  $rate = Array();
	  $merilka = '';
	  $merstr = '';

	  foreach (AluExchRates::GetList('b_alutech_exchange_rates') as $v)
    {
      // Получаем из БД сохраненный ранее "Курс Алютех"
      if($v['CHARCODE'] == 'rateAlutec')
	      $merilka = $v['RATE'];

	    // Получаем из БД курс Евро от Нацбанка
	    if($v['CHARCODE'] == 'EUR')
		    $conversionRate = $v['RATE'];
    }
	  // Сравниваем с полученным из формы, и если отличается, то берем из результатов формы
    if($_POST['rateAlutec'] != $merilka)
	    $merstr = ', RATE = "'.$_POST['rateAlutec'].'", DATE_UPDATE = "'.date('Y-m-d H:i:s').'"';

	  // Формируем строки для обновления БД
	  switch ($_POST['rateAlutecRadio'])
	  {
		  case 'EUR':
			  $rate = array(
				  'EUR' => 'ACTIVE = "Y"',
				  'rateAlutec' => 'ACTIVE = ""'.$merstr
			  );
			  break;
		  case 'rateAlutec':
			  $rate = array(
				  'EUR' => 'ACTIVE = ""',
				  'rateAlutec' => 'ACTIVE = "Y"'.$merstr
			  );
			  // Если активен "Курс Алютех", то берем его для пересчета перед сохранением в БД Евро в рубли
			  $conversionRate = $_POST['rateAlutec'];
			  break;
	  }

	  // Обновляем таблицу с курсами. Выбираем активный для подсчета и если необходимо, то корректируем "Курс Алютех" в БД
	  if(count($rate))
		  AluExchRates::Update('b_alutech_exchange_rates', $rate);
  }

	$arProducts = array();
	$arGroups = array();
	$arItems = array();
	$keyProducts = '';
	$keyGroups = '';
	$resPro = '';
	$resGro = '';
	$resIt = '';
	// Формируем массивы для добавления в таблицы БД
	foreach ($_POST as $postKey => $postValue)
  {
	  preg_match ("/EXRT_PROPERTY_(\d*)_([A-Z_]*)/", $postKey, $output_array);
	  
	  switch ($output_array[2])
	  {
		  case 'PRODUCTS_GROUP':
			  if(!empty($postValue))
			  {
				  $arProducts[$output_array[1]]['column'] .= 'ID, NAME';
				  $arProducts[$output_array[1]]['value'] .= '"'.$output_array[1].'", "'.$postValue.'"';
				  $arProducts[$output_array[1]]['update'] .= 'NAME="'.$postValue.'"';
			  }
			  $keyProducts = empty($postValue) ? '' : $output_array[1];
			  break;

		  case 'GROUP_NAME':
			  if(!empty($postValue) && $keyProducts != '')
			  {
				  $arGroups[$output_array[1]]['column'] .= 'ID, NAME';
				  $arGroups[$output_array[1]]['value'] .= '"'.$output_array[1].'", "'.$postValue.'"';
				  $arGroups[$output_array[1]]['update'] .= 'NAME="'.$postValue.'"';
			  }
			  $keyGroups = empty($postValue) ? '' : $output_array[1];
			  break;
	  }

	  if( !empty( $postValue ) && $keyProducts != '' && $keyGroups != '' )
	  {
		  switch ($output_array[2])
		  {
			  case 'NAME':
				  $postValue = preg_replace("/[\"']*/", "", $postValue);
				  $arItems[$output_array[1]]['column'] .= 'ID, PRODUCTS_GROUP, GROUP_NAME, NAME';
				  $arItems[$output_array[1]]['value'] .= '"'.$output_array[1].'", "'.$keyProducts.'", "'.$keyGroups.'", "'.$postValue.'"';
				  $arItems[$output_array[1]]['update'] .= 'PRODUCTS_GROUP="'.$keyProducts.'", GROUP_NAME="'.$keyGroups.'", NAME="'.$postValue.'"';
				  break;

			  case 'EUR_RATE':
				  $arItems[$output_array[1]]['column'] .= ', EUR_RATE';
				  $arItems[$output_array[1]]['value'] .= ', "'.$postValue.'"';
				  $arItems[$output_array[1]]['update'] .= ', EUR_RATE="'.$postValue.'"';
				  break;

			  case 'BYN_RATE':
				  $eurRate = $_POST[$strPREFIX_PROPERTY.$output_array[1].'_EUR_RATE'];
				  $bynRate = ceil( $eurRate * $conversionRate );
				  $arItems[$output_array[1]]['column'] .= ', BYN_RATE';
				  $arItems[$output_array[1]]['value'] .= ', "'.$bynRate.'"';
				  $arItems[$output_array[1]]['update'] .= ', BYN_RATE="'.$postValue.'"';
				  break;

			  case 'CODE':
				  $arItems[$output_array[1]]['column'] .= ', CODE';
				  $arItems[$output_array[1]]['value'] .= ', "'.$postValue.'"';
				  $arItems[$output_array[1]]['update'] .= ', CODE="'.$postValue.'"';
				  break;

			  case 'DEL':
				  unset($arItems[$output_array[1]]);
				  break;
		  }
	  }
  }

  // Обновляем записи, или добавляем, если они новые.
	$resPro = AluExchRates::Add('b_alutech_exchange_rates_product', $arProducts);
	if( $resPro )
	{
		$resGro = AluExchRates::Add('b_alutech_exchange_rates_group', $arGroups);
		if( $resGro )
		{
			$resIt = AluExchRates::Add('b_alutech_exchange_rates_items', $arItems);
		}
	}
	if( $resIt )
	{
		// если сохранение прошло удачно - перенаправим на новую страницу
		// (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
		if ($apply != "")
			// если была нажата кнопка "Применить" - отправляем обратно на форму.
			LocalRedirect("/bitrix/admin/alutech_exchange_rates_admin.php?mess=ok&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			// если была нажата кнопка "Сохранить" - отправляем к списку элементов.
			LocalRedirect("/bitrix/admin/alutech_exchange_rates_admin.php?lang=".LANG);
  }
}

// ******************************************************************** //
//                ВЫБОРКА И ПОДГОТОВКА ДАННЫХ ФОРМЫ                     //
// ******************************************************************** //

// выборка данных
$dbExchRatRes = AluExchRates::GetList('b_alutech_exchange_rates');
$dbProducts = AluExchRates::GetList('b_alutech_exchange_rates_product');
$dbGroups = AluExchRates::GetList('b_alutech_exchange_rates_group');
$dbItems = AluExchRates::GetList('b_alutech_exchange_rates_items');

if(!empty($dbItems))
{
	foreach ($dbItems as $valItems)
	{
		if(!array_key_exists($valItems['PRODUCTS_GROUP'], $arExchRatItems))
		{
			$arExchRatItems[$valItems['PRODUCTS_GROUP']] = array(
				'NAME' => $dbProducts[$valItems['PRODUCTS_GROUP']]['NAME'],
				'GROUP' => array()
			);
		}
		if(!array_key_exists($valItems['GROUP_NAME'], $arExchRatItems[$valItems['PRODUCTS_GROUP']]['GROUP']))
			$arExchRatItems[$valItems['PRODUCTS_GROUP']]['GROUP'][$valItems['GROUP_NAME']] = array(
				'NAME' => $dbGroups[$valItems['GROUP_NAME']]['NAME'],
				'ITEMS' => array()
			);
		$arExchRatItems[$valItems['PRODUCTS_GROUP']]['GROUP'][$valItems['GROUP_NAME']]['ITEMS'][$valItems['ID']] = $valItems;
		$arFindItems[] = $valItems['CODE'];

	}
}

// ******************************************************************** //
//                ВЫВОД ФОРМЫ                                           //
// ******************************************************************** //

// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage("EXCH_RATE_TITLE"));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok")
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("EXCH_RATE_SAVED"), "TYPE"=>"OK"));

?><script type="text/javascript">
  var ExchRatRes;
  var ItemsCode = new Array();
  var ProductsId = <? echo( end( $dbProducts)['ID'] != '' ? end($dbProducts)['ID'] : '0' ); ?>;
  var GroupsId = <? echo( end( $dbGroups)['ID'] != '' ? end($dbGroups)['ID'] : '0' ); ?>;
  var ItemsId = <? echo( end( $dbItems)['ID'] != '' ? end($dbItems)['ID'] : '0' ); ?>;
	<?foreach ($arFindItems as $key => $value){?>
  ItemsCode[<? echo $key; ?>] = "<? echo $value; ?>";
  <?}?>
  var mess1 = '<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_GROUP_ROW'); ?>';
  var mess2 = '<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_GROUP_ROW_DESCR'); ?>';
  var mess3 = '<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_ROW'); ?>';
  var mess4 = '<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_ROW_DESCR'); ?>';
</script><?
// далее выводим собственно форму
?><form method="POST" name="exchratform" id="exchratform" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data"><?
// проверка идентификатора сессии
echo bitrix_sessid_post();

// отобразим заголовки закладок
$tabControl->Begin();

//********************
// первая закладка - курсы валют
//********************
$tabControl->BeginNextTab();

foreach ($dbExchRatRes as $valExchRatRes )
{
  ?>
  <tr>
    <td style="width: 150px; "><? echo $valExchRatRes['NAME']; ?>:</td>
    <td style="width: 20px;">
      <input
          name="rateAlutecRadio"
          value="<? echo $valExchRatRes['CHARCODE']; ?>"
          type="radio"
        <?
        if($valExchRatRes['ACTIVE'] == "Y")
        {
          $ExChRate = $valExchRatRes["RATE"];
	        echo " checked";
        }
        ?>
      >
    </td>
    <td>
      <input
          name="<? echo ($valExchRatRes['CHARCODE']); ?>"
          value="<? echo $valExchRatRes['RATE']; ?>"
          type="text"
        <?if($valExchRatRes['CHARCODE'] != "rateAlutec") echo "  disabled='disabled'"?>
      >
      Обновлено: <? echo $valExchRatRes['DATE_UPDATE']; ?>
    </td>
  </tr>
  <?
}

//********************
// вторая закладка - цены на продукцию
//********************
$tabControl->BeginNextTab();
?>
  <tr>
    <td>
      <script type="text/javascript">
        ExchRatRes = '<? echo $ExChRate; ?>';
      </script>
      <table class="internal">
        <col width="5%" />
        <col width="45%" />
        <col width="15%" />
        <col width="15%" />
        <col width="15%" />
        <col width="5%" />
        <tr class="heading">
          <td>ID</td>
          <td><? echo GetMessage("EXCH_RATE_PROP_NAME_SHORT"); ?></td>
          <td><? echo GetMessage("EXCH_RATE_PROP_EXCH_RATE_EURO"); ?></td>
          <td><? echo GetMessage("EXCH_RATE_PROP_EXCH_RATE_BYN"); ?></td>
          <td><? echo GetMessage("EXCH_RATE_PROP_CODE_SHORT"); ?></td>
          <td><? echo GetMessage("EXCH_RATE_PROP_DELETE_SHORT"); ?></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table id="tableProductsList" style="width: 100%;border-spacing: 0 30px;">
	      <?

	      $strResultItems = '';
	      foreach ($arExchRatItems as $key => $value)
	      {

		      $strResultItems .= '<tr><td style="border: 1px solid #d0d7d8 !important;"><table style="width: 100%;border-spacing: 0 30px;" id="product_group_'.$key.'">';
		      // Заголовок продуктовой группы
		      $strResultItems .= '<tr class="heading"><td align="center">'.GetMessage("EXCH_RATE_TITLE_PRODUCT").'<input
          type="text"
          size="80"
          maxlength="255"
          name="'.$strPREFIX_PROPERTY.$key.'_PRODUCTS_GROUP"
          id="'.$strPREFIX_PROPERTY.$key.'_PRODUCTS_GROUP"
          value="'.$value['NAME'].'"
      ></td></tr>';

		      if(!empty($value['GROUP']))
		      {

			      foreach ($value['GROUP'] as $keyGroup => $valueGroup)
			      {
				      $strResultItems .= '<tr><td><table class="internal" style="margin: 0 auto; width: 100%;" id="group_name_'.$keyGroup.'"><col width="5%" /><col width="45%" /><col width="15%" /><col width="15%" /><col width="15%" /><col width="5%" />';
				      // Заголовок подгруппы
				      $strResultItems .= '<tr class="heading"><td colspan="6" align="center">'.GetMessage("EXCH_RATE_TITLE_GROUP").'<input
                type="text"
                size="80"
                maxlength="255"
                name="'.$strPREFIX_PROPERTY.$keyGroup.'_GROUP_NAME"
                id="'.$strPREFIX_PROPERTY.$keyGroup.'_GROUP_NAME"
                value="'.$valueGroup['NAME'].'"
            ></td></tr>';
				      if(!empty($valueGroup['ITEMS']))
				      {
					      foreach ($valueGroup['ITEMS'] as $keyItems => $valueItems)
					      {
						      // Строки с ценами
						      $strResultItems .= __AddPropRow($keyItems, $strPREFIX_PROPERTY, $valueItems, $ExChRate);
					      }
				      }
				      $groupNameId = 'group_name_'.$keyGroup;

				      $strResultItems .= '</table><div style="width: 100%; text-align: center; margin: 10px 0;"><input class="adm-btn-big" onclick="addItemsListRow(ItemsId, '."'".$groupNameId."'".');" type="button" value="'.GetMessage('EXCH_RATE_SHOW_ADD_PROP_ROW').'" title="'.GetMessage('EXCH_RATE_SHOW_ADD_PROP_ROW_DESCR').'"></div></td></tr>';

			      }

		      }
		      $prodGroupId = 'product_group_'.$key;

		      $strResultItems .= '</table><div style="width: 100%; text-align: center; margin: 10px 0;"><input class="adm-btn-big" onclick="addGroupListRow(mess3, mess4, GroupsId, '."'".$prodGroupId."'".');" type="button" value="'.GetMessage('EXCH_RATE_SHOW_ADD_PROP_GROUP_ROW').'" title="'.GetMessage('EXCH_RATE_SHOW_ADD_PROP_GROUP_ROW_DESCR').'"></div></td></tr>';
	      }

	      echo $strResultItems;

	      ?>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center">
      <div style="width: 100%; text-align: center; margin: 10px 0;">
        <input
            class="adm-btn-big"
            onclick="addProductsListRow(mess1, mess2, ProductsId);"
            type="button"
            value="<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_PRODUCTS_ROW'); ?>"
            title="<? echo GetMessage('EXCH_RATE_SHOW_ADD_PROP_PRODUCTS_ROW_DESCR'); ?>"
        >
      </div>
    </td>
  </tr>
<?

// завершение формы - вывод кнопок сохранения изменений
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"alutech_exchange_rates_admin.php?lang=".LANG,

	)
);
?>
  <input type="hidden" name="lang" value="<?=LANG?>">
<?
// завершаем интерфейс закладок
$tabControl->End();

// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>