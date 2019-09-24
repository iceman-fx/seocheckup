<?php
/*
	Redaxo-Addon SEO-CheckUp
	Deinstallation
	v1.1.3
	by Falko Müller @ 2019
	package: redaxo5
*/

//Variablen deklarieren
$mypage = $this->getProperty('package');
$error = ""; $notice = "";


//Datenbank-Einträge löschen
rex_sql_table::get(rex::getTable('article'))
	->removeColumn('seocu_keyword')
	->removeColumn('seocu_result')
	->removeColumn('seocu_data')
    ->alter();


//Module löschen
//$notice .= $I18N->msg('a1544_deletemodule');	//'Bitte löschen Sie die installierten Addon-Module von Hand.<br />';


//Aktionen löschen
//$notice .= 'Bitte löschen Sie die installierten Addon-Aktionen von Hand.<br />';


//Templates löschen
//$notice .= 'Bitte löschen Sie die installierten Addon-Templates von Hand.<br />';
?>