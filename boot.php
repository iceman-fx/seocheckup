<?php
/*
	Redaxo-Addon SEO-CheckUp
	Boot (weitere Konfigurationen)
	v1.6
	by Falko Müller @ 2019-2021
	package: redaxo5
	
	Info:
	Basisdaten wie Autor, Version, Subpages etc. werden in der package.yml notiert.
	Klassen und lang-Dateien werden automatisch gefunden (Ordnernamen beachten).
	Dateibasierte Konfigurationswerte nicht hier vornehmen !!! -> rex_config dafür nutzen (siehe install.php) !!!
*/

//Variablen deklarieren
$mypage = $this->getProperty('package');
//$this->setProperty('name', 'Wert');

	//Berechtigungen deklarieren
	if (rex::isBackend() && is_object(rex::getUser())):
		rex_perm::register($mypage.'[]');
		//rex_perm::register($mypage.'[admin]');
	endif;


//Userrechte prüfen
$isAdmin = ( is_object(rex::getUser()) AND (rex::getUser()->hasPerm($mypage.'[admin]') OR rex::getUser()->isAdmin()) ) ? true : false;


//Addon Einstellungen
$config = rex_addon::get($mypage)->getConfig('config');			//Addon-Konfig einladen
/*
if (!$this->hasConfig()):
    $this->setConfig('url', 'http://www.example.com');
    $this->setConfig('ids', [1, 4, 5]);
endif;
*/


//Funktionen einladen/definieren
//Global für Backend+Frontend
global $a1544_mypage;
$a1544_mypage = $mypage;

global $a1544_darkmode;
$a1544_darkmode = (rex_string::versionCompare(rex::getVersion(), '5.13.0-dev', '>=')) ? true : false;


require_once(rex_path::addon($mypage)."/functions/functions.inc.php");

//Backendfunktionen
if (rex::isBackend() && rex::getUser()):
	//Navigation bearbeiten
	$page = $this->getProperty('page');
	
		//Sprachauswahl zur Navigation hinzufügen
		if (count(rex_clang::getAll(false)) > 1):
			$cids = rex_clang::getAll();
			
			foreach ($cids as $id => $cid):
				if (rex::getUser()->getComplexPerm('clang')->hasPerm($id)):
					$page['subpages']['default']['subpages']['clang-'.$id] = ['title' => $cid->getName()];
				endif;
			endforeach;
		endif;
		
		
		// rex_string::versionCompare( preg_replace("/[^0-9\.]+/i", "", rex_addon::get('url')->getVersion() ), '2.0.0', '>=')
		
		//URL-Addon in Navigation berücksichtigen
		if ( rex_addon::get('url')->isAvailable() && rex_string::versionCompare( rex_addon::get('url')->getVersion(), '2.0.0-dev', '>=') ):
			//URL-Addon-Profile als Subnavi ausgeben
			$sql = "SELECT DISTINCT(t1.profile_id), t2.namespace FROM ".rex::getTable('url_generator_url')." AS t1 INNER JOIN ".rex::getTable('url_generator_profile')." AS t2 ON t1.profile_id = t2.id ORDER BY t2.namespace ASC, t1.profile_id ASC";
			$db = rex_sql::factory();
			$db->setQuery($sql);

			if ($db->getRows() > 0):
				for ($i=0; $i < $db->getRows(); $i++):
					$eid = intval($db->getValue('t1.profile_id'));
					$title = aFM_maskChar($db->getValue('t2.namespace'));
					
					$page['subpages']['urlcheckup']['subpages']['urlprofile-'.$eid] = ['title' => $title];
					
					$db->next();
				endfor;
			endif;
			
		else:
			//URL-Addon ab v2.x nicht verfügbar
			unset($page['subpages']['urlcheckup']);
		endif;
		
	$this->setProperty('page', $page);
	
	
	//AJAX anbinden
	$ajaxPages = array('load-articlelist', 'load-urllist');
		if (rex_be_controller::getCurrentPagePart(1) == $mypage && in_array(rex_request('subpage', 'string'), $ajaxPages)):
			rex_extension::register('OUTPUT_FILTER', 'aFM_bindAjax');
		endif;	
	
	
	//SEO-CheckUp einbinden
	require_once(rex_path::addon($mypage)."/functions/functions_be_seo.inc.php");
	
	rex_view::addCssFile($this->getAssetsUrl('style.css'));
	if ($a1544_darkmode) { rex_view::addCssFile($this->getAssetsUrl('style-darkmode.css')); }
	
	rex_view::addJsFile($this->getAssetsUrl('script.js'));
	rex_view::addCssFile($this->getAssetsUrl('chartjs/Chart.min.css'));
	rex_view::addJsFile($this->getAssetsUrl('chartjs/Chart.min.js'));
	
	rex_extension::register('OUTPUT_FILTER', 'a1544_seocuJS');

	if (@$config['be_seo'] == "checked"):
		//Sidebar-CheckUp
		rex_extension::register('PACKAGES_INCLUDED', function($ep){
			global $a1544_mypage;
			$config = rex_addon::get($a1544_mypage)->getConfig('config');
			
			if (@$config['be_seo_sidebar_priority'] == "checked"):
				rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'a1544_showSEO', rex_extension::LATE);
			else:
				rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', 'a1544_showSEO', rex_extension::EARLY);
			endif;
			
			//bei Contentänderung Info über URL bereitstellen
			foreach(array("SLICE_ADDED", "SLICE_DELETED", "SLICE_UPDATED") as $e):
				rex_extension::register($e, function($ep){ $op = $ep->getSubject(); $cnt = "<script>window.location.replace(window.location.href+'&seocucnt=changed');</script>"; return $op.$cnt; }, rex_extension::EARLY);
			endforeach;
		}, rex_extension::LATE);
	endif;

endif;

//Frontendfunktionen
if (!rex::isBackend()):
	//require_once(rex_path::addon($mypage)."/functions/functions_fe.inc.php");
	
	//CSS/Skripte einbinden
	//rex_extension::register('OUTPUT_FILTER', 'a1544_addAssets');
endif;
?>