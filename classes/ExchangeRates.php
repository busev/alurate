<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 03.11.2017
 * Time: 15:36
 */

class exchangeRates {

	function alurateAgent()
	{
		exchangeRates::updateAllCurrencyBaseRate();
		return 'exchangeRates::alurateAgent();';
	}

	function updateAllCurrencyBaseRate()
	{
		global $DB;

		$kursi = @simplexml_load_file('http://www.nbrb.by/Services/XmlExRates.aspx');
		if ($kursi) {
			foreach ($kursi as $Currency) {
				if ($Currency->CharCode == 'EUR') {
					$rate = (string)$Currency->Rate;
					$charcode = $Currency->CharCode;
				}
			}
		}

		if ($rate === '')
			return;

		$query = "
			UPDATE b_alutech_exchange_rates
			SET RATE = '".$rate."', DATE_UPDATE = '".date('Y-m-d H:i:s')."'
			WHERE CHARCODE = '".$charcode."'";

		$DB->Query($query, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);

		exchangeRates::updateAllCurrencyEuro();

	}

	function updateAllCurrencyEuro()
	{
		global $DB;
		$strSql = "SELECT RATE FROM b_alutech_exchange_rates WHERE ACTIVE = 'Y'";
		$dbr = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
		while($res = $dbr->Fetch())
			exchangeRates::updateAllCurrencyItems($res['RATE']);
	}

	function updateAllCurrencyItems($rate)
	{
		global $DB;
		$strSql = "SELECT ID, EUR_RATE FROM b_alutech_exchange_rates_items ORDER BY ID";
		$dbr = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
		while($res = $dbr->Fetch())
			exchangeRates::updateAllCurrencyByn($res['ID'], ceil( $res['EUR_RATE'] * $rate ));

	}

	function updateAllCurrencyByn($id, $param)
	{
		global $DB;
		$strSql = "UPDATE b_alutech_exchange_rates_items SET BYN_RATE = ".$param." WHERE ID = '".$id."'";
		$DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
	}


	function changePriceContent(&$content)
	{
		global $USER, $APPLICATION;
		if( !$USER->IsAdmin() || ( $USER->IsAdmin() && strpos( $APPLICATION->GetCurDir(), "/bitrix/admin/" ) === FALSE ) )
			$content = exchangeRates::replacementOfPrices($content, exchangeRates::getListReplacement());
	}

	function replacementOfPrices($buffer, $replacements)
	{
		return preg_replace(array_keys($replacements), array_values($replacements), $buffer);
	}

	function getListReplacement ()
	{
		/*$replacements = array(
			[#asg600_3kit-l#] => 303
      [#asg1000_3kit-l#] => 358
      [#asg1000_4kit#] => 418
		);*/
		global $DB;
		$replacements = array();
		$strSql = "SELECT CODE, BYN_RATE FROM b_alutech_exchange_rates_items";
		$dbr = $DB->Query($strSql, false, 'File: '.__FILE__.'<br>Line: '.__LINE__);
		while($res = $dbr->Fetch())
			$replacements['/#'.$res['CODE'].'#/'] = $res['BYN_RATE'];

		return $replacements;
	}

}

?>