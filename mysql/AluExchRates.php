<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 27.10.2017
 * Time: 12:45
 */

class AluExchRates {

	/*************** ADD, UPDATE, DELETE *****************/
	function Add($dbName, $arFields)
	{
		$err_mess = (AluExchRates::err_mess())."<br>Function: Add<br>Line: ";
		global $DB;

		if(count($arFields))
		{
			//$DB->Query("TRUNCATE TABLE ".$dbName);
			foreach ($arFields as $content)
			{
				if( strlen( $content["column"] ) > 0 && strlen( $content["value"] ) > 0 && strlen( $content["update"] ) > 0 )
				{

					$strSql =
						"INSERT INTO ".$dbName."(".$content["column"].") ".
						"VALUES(".$content["value"].") ".
						"ON DUPLICATE KEY UPDATE ".$content["update"];
					$res = $DB->Query($strSql, False, $err_mess.__LINE__);
				}
			}
			//return IntVal($DB->LastID());
			return $res->result;
		}
		else
		{
			$DB->Query("TRUNCATE TABLE ".$dbName);
		}
		return TRUE;
	}

	function Update($dbName, $arFields)
	{
		global $DB;

		$selection = 'ID';
		if($dbName == 'b_alutech_exchange_rates')
			$selection = 'CHARCODE';

		if(count($arFields))
		{
			foreach ($arFields as $id => $content) {
				$strSql =
					"UPDATE ".$dbName." SET ".
					"	".$content." ".
					"WHERE ".$selection." = '".$id."' ";
				$DB->Query($strSql, False, 'File: '.__FILE__.'<br>Line: '.__LINE__);
			}
		}
	}

	//*************** SELECT *********************/
	function GetList($dbName)
	{
		global $DB;
		$strSql =
			"SELECT * ".
			"FROM ".$dbName." ".
			"ORDER BY ID";
		$dbr = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
		while($res = $dbr->Fetch())
			$arRes[$res['ID']] = $res;
		return $arRes;
	}

	function err_mess()
	{
		$module_id = "alurate";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: Add<br>File: ".__FILE__;
	}

}