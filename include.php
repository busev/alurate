<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 27.10.2017
 * Time: 12:38
 */

global $DBType;
$arClasses = array(
	"AluExchRates" => $DBType."/AluExchRates.php",
	"exchangeRates" => "classes/ExchangeRates.php"
);
CModule::AddAutoloadClasses("alurate", $arClasses);
?>