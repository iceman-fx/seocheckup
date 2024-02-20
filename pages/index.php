<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: index
	v1.6.8
	by Falko Müller @ 2019-2024
	package: redaxo5
*/

//Fehlerhinweise (E_NOTICE) abschalten
//error_reporting(E_ALL ^  E_NOTICE);

//Variablen deklarieren
$mypage = $this->getProperty('package');

$page = rex_request('page', 'string');
$subpage = rex_be_controller::getCurrentPagePart(2);												//Subpages werden aus page-Pfad ausgelesen (getrennt mit einem Slash, z.B. page=demo_addon/subpage -> 2 = zweiter Teil)
	$tmp = rex_request('subpage', 'string');
	$subpage = (!empty($tmp)) ? $tmp : $subpage;
$subpage2 = rex_be_controller::getCurrentPagePart(3);												//2. Unterebene = dritter Teil des page-Parameters
	$subpage2 = (!empty($subpage2)) ? preg_replace("/.*-([0-9])$/i", "$1", $subpage2) : '';

$func = rex_request('func', 'string');

	
//Addon-Config einlesen für Listenausgaben
$config = $this->getConfig('config');
	$config['be_seo_offlinearts'] 			= (@$config['be_seo_offlinearts'] == 'checked') 					? true : false;
	$config['be_seo_culist_title']			= (@$config['be_seo_culist_title'] == 'checked') 					? true : false;
	$config['be_seo_culist_desc']			= (@$config['be_seo_culist_desc'] == 'checked') 					? true : false;
	$config['be_seo_culist_snippet']		= (@$config['be_seo_culist_snippet'] == 'checked') 					? true : false;
	$config['be_seo_culist_h1']				= (@$config['be_seo_culist_h1'] == 'checked') 						? true : false;
	$config['be_seo_culist_h2']				= (@$config['be_seo_culist_h2'] == 'checked') 						? true : false;
	$config['be_seo_culist_links']			= (@$config['be_seo_culist_links'] == 'checked') 					? true : false;
	$config['be_seo_culist_words']			= (@$config['be_seo_culist_words'] == 'checked') 					? true : false;
	
	$is_allchecks							= (@$config['be_seo_checks_selection'] != 'checked') 				? true : false;
	$config['be_seo_culist_wdf']			= (@$config['be_seo_culist_wdf'] == 'checked') 						? true : false;
	$config['be_seo_checks_links']			= (@$config['be_seo_checks_links'] == 'checked' || $is_allchecks) 	? true : false;
	$config['be_seo_checks_wdf']			= (@$config['be_seo_checks_wdf'] == 'checked' || $is_allchecks) 	? true : false;


//Userrechte prüfen
//$isAdmin = ( is_object($REX['USER']) AND ($REX['USER']->hasPerm($mypage.'[admin]') OR $REX['USER']->isAdmin()) ) ? true : false;


//Seitentitel ausgeben
echo ($subpage != 'cropper') ? rex_view::title($this->i18n('a1544_title').'<span class="addonversion">'.$this->getProperty('version').'</span>') : '';


//globales Inline-CSS + Javascript
?>
<style type="text/css">
input.rex-form-submit { margin-left: 190px !important; }	/* Rex-Button auf neue (Labelbreite +10) verschieben */
td.name { position: relative; padding-right: 20px !important; }
.nowidth { width: auto !important; }
.togglebox { display: none; margin-top: 8px; font-size: 90%; color: #666; line-height: 130%; }
.toggler { width: 15px; height: 12px; position: absolute; top: 10px; right: 3px; }
.toggler a { display: block; height: 11px; background-image: url(../assets/addons/<?php echo $mypage; ?>/arrows.png); background-repeat: no-repeat; background-position: center -6px; cursor: pointer; }
.required { font-weight: bold; }
.inlinelabel { display: inline !important; width: auto !important; float: none !important; clear: none !important; padding: 0px  !important; margin: 0px !important; font-weight: normal !important; }
.inlineform { display: inline-block !important; }
.form_auto { width: auto !important; }
.form_plz { width: 25%px !important; margin-right: 6px; }
.form_ort { width: 73%px !important; }
.form_25perc { width: 25% !important; min-width: 120px; }
.form_50perc { width: 50% !important; min-width: 120px; }
.form_75perc { width: 75% !important; }
.form_content { display: block; padding-top: 5px; }
.form_readonly { background-color: #EEE; color: #999; }
.form_isoffline { color: #A00; }
.addonversion { margin-left: 7px; }
.radio label, .checkbox label { margin-right: 20px; }
.spacerline { display: block; height: 7px; margin-bottom: 15px; }
.cur-p { cursor: pointer; }
.cur-d { cursor: default; }

.form_column, .datepicker-widget { display: inline-block; vertical-align: middle; }
	.form_column-spacer, .datepicker-widget-spacer { padding: 0px 5px; }
.daterangepicker { box-shadow: 3px 3px 10px 0px rgb(0,0,0, 0.2); }
.daterangepicker .calendar-table th, .daterangepicker .calendar-table td { padding: 2px; }

.form_2spaltig > div { display: inline-block; width: 49%; }

.addon_failed, .addonfailed { color: #F00; font-weight: bold; margin-bottom: 15px; }
.addon_search { width: 100%; background-color: #EEE; }
.addon_search .searchholder { position: relative; display: inline-block; }
	.addon_search .searchholder a { position: absolute; top: 0px; right: 0px; bottom: 0px; cursor: pointer; padding: 5px 3px 0px; }
		.addon_search .searchholder img { vertical-align: top; }
	@-moz-document url-prefix('') { .addon_search .searchholder a { top: 0px; } /* FF-only */ }
.addon_search .border-top { border-top: 1px solid #DFE9E9; }
.addon_search td { width: 46%; padding: 9px !important; font-size: 90%; color: #333; border: none !important; vertical-align: top !important; }
	.addon_search td.td2 { width: 8%; text-align: center; }
	.addon_search td.td3 { text-align: right; }

.addon_search .form-control { padding: 1px 8px; font-size: 13px; float: none; }
.addon_search .form-control-btn { padding: 2px 8px; font-size: 12px; }

.addon_search .input-group.sbeg { margin-left: auto; }
@media (min-width: 768px){ .addon_search .input-group.sbeg { max-width: 180px; } }
		
.addon_search select { margin: 0px !important; padding: 0px 10px 0px 0px !important; height: 20px !important; min-width: 230px; max-width: 230px; }
	.addon_search select option { margin-right: -10px; padding-right: 10px; }
	.addon_search select.multiple { height: 60px !important; }
	.addon_search select.form_auto { width: auto !important; max-width: 634px; }
.addon_search input.checkbox { display: inline-block; width: auto; margin: 0px 6px !important; padding: 0px !important; height: auto !important; }
.addon_search input.button { font-weight: bold; margin: 0px !important; width: auto; padding: 0px 4px !important; height: 22px !important; font-size: 0.9em; background: #FFF; border: 1px solid #323232; }
.addon_search label { display: inline-block; width: 90px !important; font-weight: normal; }
	.addon_search label.multiple { vertical-align: top !important; }
	.addon_search label.form_auto { width: auto !important; }
.addon_search a.moreoptions { display: inline-block; vertical-align: sub; }
.addon_search .rightmargin { margin-right: 7px !important; }

.addon_inlinegroup { display: inline-block; }
.addon_input-group { display: table; }
	.addon_input-group > * { display: table-cell; border-radius: 0px; border: 1px solid #7586a0; margin-left: -1px; }
	.addon_input-group > *:first-child { margin: 0px; }
	.addon_input-group > *:last-child { border-radius: 0px 2px 2px 0px; }
	
.addon_input-group-field {}
.addon_input-group-btn {}

.db-order { display: inline; /*width: 20px; height: 10px;*/ padding: 0px 5px; margin-left: 0px; cursor: pointer; }
.db-order-desc { background-position: center bottom; }
.block { display: block; }
.info { font-size: 0.825em; }
.info-labels { display: inline-block; padding: 3px 6px; background: #EAEAEA; margin-right: 5px; font-size: 0.80em; }
	.info-green { background: #360; color: #FFF; }
	.info-red { background: #900; color: #FFF; }
.infoblock { display: block; font-size: 0.825em; margin-top: 7px; }

span.ajaxNav { /*display: inline-block; padding: 2px 4px; margin: 3px 2px 1px;*/ cursor: pointer; }
span.ajaxNav:hover { background-color: #666; color: #FFF; }
span.ajaxNavSel { background-color: #CCC; }

.checkbox.toggle label input, .radio.toggle label input { -webkit-appearance: none; -moz-appearance: none; appearance: none; width: 3em; height: 1.5em; background: #ddd; vertical-align: middle; border-radius: 1.6em; position: relative; outline: 0; margin-top: -3px; margin-right: 10px; cursor: pointer; transition: background 0.1s ease-in-out; }
	.checkbox.toggle label input::after, .radio.toggle label input::after, .radio.switch label input::before { content: ''; width: 1.5em; height: 1.5em; background: white; position: absolute; border-radius: 1.2em; transform: scale(0.7); left: 0; box-shadow: 0 1px rgba(0, 0, 0, 0.5); transition: left 0.1s ease-in-out; }
.checkbox.toggle label input:checked, .radio.toggle label input:checked { background: #5791CE; }
	.checkbox.toggle label input:checked::after { left: 1.5em; }

.radio.switch label { margin-right: 1.5em; }
.radio.switch label input { width: 1.5em; margin-right: 5px; }
	.radio.switch label input:checked::after { transform: scale(0.5); }
.radio.switch label input::before { background: #5791CE; opacity: 0; box-shadow: none; }
	.radio.switch label input:checked::before { animation: radioswitcheffect 0.65s; }
@keyframes radioswitcheffect { 0% { opacity: 0.75; } 100% { opacity: 0; transform: scale(2.5); } }

.optionsblock { display: inline-block; vertical-align: top; min-width: 182px; margin: 0px 23px 18px 0px; padding: 7px 14px; transition: all .3s ease; }
	.optionsblock:hover { background: #FFF; }
.optionsblock label { margin-right: 0px !important; }
.optionsblock ul { list-style: none; margin: 0px; padding: 0px;}
.optionsblock li { margin: 0px 0px 5px; }


<?php if (rex_string::versionCompare(rex::getVersion(), '5.13.0-dev', '>=')): ?>
@media (prefers-color-scheme: dark){
	
	body:not(.rex-theme-light) .checkbox.toggle label input,
	body:not(.rex-theme-light) .radio.toggle label input
		{ background: #202b35; }
	body:not(.rex-theme-light) .checkbox.toggle label input::after, 
	body:not(.rex-theme-light) .radio.toggle label input::after, 
	body:not(.rex-theme-light) .radio.switch label input::before
		{ background: #CCC; }
	
	body:not(.rex-theme-light) .checkbox.toggle label input:checked,
	body:not(.rex-theme-light) .radio.toggle label input:checked 
		{ background: #409be4; }
	body:not(.rex-theme-light) .checkbox.toggle label input:checked::after, 
	body:not(.rex-theme-light) .radio.toggle label input:checked::after, 
	body:not(.rex-theme-light) .radio.switch label input:checked::before
		{ background: #EEE; }
}
<?php endif; ?>
</style>


<script type="text/javascript">
setTimeout(function() { jQuery('.alert-info').fadeOut(); }, 5000);			//Rückmeldung ausblenden

//beim Start ausführen

//Funktionen
function loadAJAX(params, dst, paramNav)
{	if (dst != ""){
		paramNav = parseInt(paramNav);
		if (params != "" && paramNav >= 0) params += '&';
			params += 'limStart='+ encodeURIComponent(paramNav);
		var jlLoader = jQuery('#ajax_loading');
			jlLoader.show();
		jQuery.post("index.php", params, function(resp){ jQuery(dst).html(resp); jlLoader.hide(); });
	}
}
</script>


<?php
//Unterseite einbinden
switch($subpage):
	case "load-articlelist":	//AJAX Loader : Default-Liste (Artikel)
								require_once("ajax.load-articlelist.inc.php");
								break;

	case "load-urllist":		//AJAX Loader : URL-Addon-Liste
								require_once("ajax.load-urllist.inc.php");
								break;

	case "urlcheckup":			//URL-Addon-Checkup
								require_once("urlcheckup.inc.php");
								break;				

	case "help":				//Hilfe
								require_once("help.inc.php");
								break;				

	case "config":				//Einstellungen
								require_once("config.inc.php");
								break;				

	default:					//Index = Artikel-Checkup
								require_once("default.inc.php");
								break;
endswitch;
?>


<!-- PLEASE DO NOT REMOVE THIS COPYRIGHT -->
<p><?php echo $this->getProperty('author'); ?></p>
<!-- THANK YOU! -->