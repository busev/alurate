<?php
/**
 * Created by PhpStorm.
 * User: busev
 * Date: 26.10.2017
 * Time: 9:58
 */
if(!check_bitrix_sessid()) return;?>
<?
echo CAdminMessage::ShowNote("Модуль успешно удален из системы");
?>