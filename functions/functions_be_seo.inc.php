<?php
/*
	Redaxo-Addon SEO-CheckUp
	Backend-Funktionen (SEO)
	v1.4
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//aktive Session prüfen


//globale Variablen
global $a1544_seps;
	$a1544_seps = " (),.!?-:/";													//Zeichen, an denen ein Wort für Zählungen getrennt wird		// [:punct:] => ! ' # S % & ' ( ) * + , - . / : ; < = > ? @ [ / ] ^ _ { | } ~


//Funktionen
//SEO-CheckUp einbinden
function a1544_showSEO($ep)
{	global $a1544_mypage;
	global $a1544_seps;
	
	//Vorgaben einlesen/setzen
	$op = $ep->getSubject();												//Content des ExtPoint (z.B. Seiteninhalt)
		$params = $ep->getParams();											//alle Parameter des ExtPoint holen (z.B. Article-ID)
	$aid = $params['article_id'];											//ID des Artikels
	$cid = $params['clang'];												//ID der Sprachversion
	$ctype = $params['ctype'];												//ID der Spalte

	$config = rex_config::get($a1544_mypage, 'config');						//Addon-Konfig einladen
	

	//SEO-Checkup Formular
	$db = rex_sql::factory();
	$db->setQuery("SELECT seocu_keyword FROM ".rex::getTable('article')." WHERE id = '".$aid."' AND clang_id = '".$cid."'");
	$keyword = $db->getValue('seocu_keyword');								//DB-Keyword einladen	
	unset($db);

	$l1 = rex_i18n::msg('a1544_seo_keyword');
	$l2 = rex_i18n::msg('a1544_seo_refresh');
	$l3 = rex_i18n::msg('a1544_seo_modal_title');
	$l4 = rex_i18n::msg('a1544_seo_modal_analyze');
	$l5 = rex_i18n::msg('a1544_seo_modal_close');
	
	
$panel = "";
$panel .= <<<EOD
	<div class="seocheckup">
		<form>
			<div class="rex-js-widget">
				<div class="input-group">
					<input class="form-control" type="text" name="seocu-keyword" value="$keyword" placeholder="$l1" data-seocu-aid="$aid" data-seocu-cid="$cid" />
					<span class="input-group-btn">
						<a class="btn btn-popup" title="$l2"><i class="rex-icon fa-refresh"></i></a>
					</span>
				</div>
			</div>
		</form>
		<div id="seocheckup"></div>
        
        <div class="modal fade bd-example-modal-lg seocu-modal" id="seocu-modal" tabindex="-1" role="dialog">
        	<div class="modal-dialog modal-dialog-centered" role"document">
            	<div class="modal-content">
                	<div class="modal-header"><div class="modal-title">$l3</div></div>
                    <div class="modal-body seocheckup">$l4</div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">$l5</button></div>
                </div>
            </div>
        </div>
	</div>
	<script>$(function(){ var seocubtn = $(".seocheckup a");	$(".seocheckup form").on('submit', function(e){ e.preventDefault(); }); seocubtn.click(function(){ seocubtn.addClass("rotate"); 	
	urldata = "rex-api-call=a1544_getSeocheckup&keyword="+encodeURIComponent($(".seocheckup input").val())+"&lasturl="+encodeURIComponent(window.location.href);
	$("#seocheckup").load("", urldata, function(){ seocubtn.removeClass("rotate"); }); }); seocubtn.trigger('click'); 
	$(document).on("rex:ready", function(){ setTimeout(function(){ seocubtn.trigger('click'); }, 1000); }); 
	});
EOD;
$panel .= '</script>';


	//SEO-Panel erstellen und ausgeben
	$collapsed = (@$config['be_seo_opened'] != 'checked') ? true : false;
	$frag = new rex_fragment();
		$frag->setVar('title', '<div class="seocu-title"><i class="rex-icon fa-stethoscope"></i> '.rex_i18n::msg('a1544_seo_head').'<div class="seocu-resultbar-wrapper"><div class="seocu-resultbar"></div></div><div class="seocu-quickinfo"></div></div>', false);
		$frag->setVar('body', $panel, false);
		$frag->setVar('article_id', $aid, false);
		$frag->setVar('clang', $cid, false);
		$frag->setVar('ctype', $ctype, false);
		$frag->setVar('collapse', true);								//schließbares Panel - true|false
		$frag->setVar('collapsed', $collapsed);							//Panel geschlossen starten - true|false
	$cnt = $frag->parse('core/page/section.php');

	return $op.$cnt;
}




//Hilfsfunktionen
//SEO-Checks
function a1544_seocheckup()
{	global $a1544_mypage;


	//set php-settings
	$time_limit = ini_get('max_execution_time');
    $memory_limit = ini_get('memory_limit');
		//set_time_limit(0);
		//ini_set('memory_limit', '-1');


	//Metadaten
	$mem_start = memory_get_usage();
	$time_start = microtime();
	

	//Variablen deklarieren
	$cnt = $analysis = "";
	$actArt = rex_request('article_id', 'int');
	$actCat = rex_request('category_id', 'int');
		$actCat = ($actArt > 0) ? 0 : $actCat;
	$actClang = rex_request('clang', 'int');
	
    $mode = (rex_request('mode') == 'json') ? 'json' : 'show';
	$getcache = (rex_request('getcache', 'int') == true) ? true : false;
		if ($getcache):
			$db = rex_sql::factory();
			$db->setQuery("SELECT seocu_data FROM ".rex::getTable('article')." WHERE id = '".$actArt."' AND clang_id = '".$actClang."'");
			if (!empty($db->getValue('seocu_data'))) { return json_encode(unserialize($db->getValue('seocu_data'))); }
			unset($db);
		endif;
	
	$keyword = mb_strtolower(urldecode(rex_request('keyword')));	
		//Keyword speichern oder einladen
        $db = rex_sql::factory();
        if (isset($_REQUEST['keyword'])):
			$db->setQuery("UPDATE ".rex::getTable('article')." SET seocu_keyword = '".aFM_maskSql($keyword)."' WHERE id = '".$actArt."' AND clang_id = '".$actClang."'");
		else:
        	//DB-Keyword einladen
            $db->setQuery("SELECT seocu_keyword FROM ".rex::getTable('article')." WHERE id = '".$actArt."' AND clang_id = '".$actClang."'");
            $keyword = $db->getValue('seocu_keyword');
		endif;
		unset($db);
	$lasturl = urldecode(rex_request('lasturl'));
	$showtests = (rex_request('showtests', 'int') == 1) ? true : false;
		

	//Vorgaben einlesen/setzen
	$config = rex_addon::get($a1544_mypage)->getConfig('config');			//Addon-Konfig einladen
		$config['be_seo_hyphenator']			= (@$config['be_seo_hyphenator'] == 'checked') 							? true : false;
		$config['be_seo_removeblock_header']	= (@$config['be_seo_removeblock_header'] == 'checked') 					? true : false;
		$config['be_seo_removeblock_footer']	= (@$config['be_seo_removeblock_footer'] == 'checked') 					? true : false;
		$config['be_seo_removeblock_nav']		= (@$config['be_seo_removeblock_nav'] == 'checked') 					? true : false;
		
		$config['be_seo_offlinekeywords'] 		= (@$config['be_seo_offlinekeywords'] == 'checked') 					? true : false;
		$config['be_seo_title_min'] 			= (!isset($config['be_seo_title_min']))									? '50' : intval($config['be_seo_title_min']);
		$config['be_seo_title_max'] 			= (!isset($config['be_seo_title_max']))									? '65' : intval($config['be_seo_title_max']);
		$config['be_seo_title_words'] 			= (!isset($config['be_seo_title_words'])) 								? '6' : intval($config['be_seo_title_words']);
		$config['be_seo_desc_min'] 				= (!isset($config['be_seo_desc_min'])) 									? '130' : intval($config['be_seo_desc_min']);
		$config['be_seo_desc_max'] 				= (!isset($config['be_seo_desc_max'])) 									? '160' : intval($config['be_seo_desc_max']);
		$config['be_seo_desc_words'] 			= (!isset($config['be_seo_desc_words'])) 								? '12' : intval($config['be_seo_desc_words']);
		$config['be_seo_content_words'] 		= (!isset($config['be_seo_content_words'])) 							? '300' : intval($config['be_seo_content_words']);
		$config['be_seo_density_min'] 			= (!isset($config['be_seo_density_min'])) 								? '300' : intval($config['be_seo_density_min']);
		$config['be_seo_density_max'] 			= (!isset($config['be_seo_density_max'])) 								? '4' : intval($config['be_seo_density_max']);
		$config['be_seo_url_max'] 				= (!isset($config['be_seo_url_max'])) 									? '55' : intval($config['be_seo_url_max']);
		$config['be_seo_url_depths'] 			= (!isset($config['be_seo_url_depths'])) 								? '3' : intval($config['be_seo_url_depths']);
		$config['be_seo_links'] 				= (!isset($config['be_seo_links'])) 									? '3' : intval($config['be_seo_links']);
		
		$is_allchecks							= (@$config['be_seo_checks_selection'] != 'checked') 					? true : false;
		$config['be_seo_checks_titledesc']		= (@$config['be_seo_checks_titledesc'] == 'checked' || $is_allchecks)	? true : false;
		$config['be_seo_checks_opengraph']		= (@$config['be_seo_checks_opengraph'] == 'checked' || $is_allchecks)	? true : false;
		$config['be_seo_checks_url']			= (@$config['be_seo_checks_url'] == 'checked' || $is_allchecks) 		? true : false;
		$config['be_seo_checks_header']			= (@$config['be_seo_checks_header'] == 'checked' || $is_allchecks) 		? true : false;
		$config['be_seo_checks_content']		= (@$config['be_seo_checks_content'] == 'checked' || $is_allchecks) 	? true : false;
		$config['be_seo_checks_links']			= (@$config['be_seo_checks_links'] == 'checked' || $is_allchecks) 		? true : false;
		$config['be_seo_checks_images']			= (@$config['be_seo_checks_images'] == 'checked' || $is_allchecks) 		? true : false;
		$config['be_seo_checks_density']		= (@$config['be_seo_checks_density'] == 'checked' || $is_allchecks) 	? true : false;
		$config['be_seo_checks_wdf']			= (@$config['be_seo_checks_wdf'] == 'checked' || $is_allchecks) 		? true : false;
		$config['be_seo_checks_flesch']			= (@$config['be_seo_checks_flesch'] == 'checked' || $is_allchecks) 		? true : false;
		$is_nochecks = (!$config['be_seo_checks_titledesc'] && !$config['be_seo_checks_opengraph'] && !$config['be_seo_checks_url'] && !$config['be_seo_checks_header'] && !$config['be_seo_checks_content'] && !$config['be_seo_checks_links'] && !$config['be_seo_checks_images'] && !$config['be_seo_checks_density'] && !$config['be_seo_checks_wdf'] && !$config['be_seo_checks_flesch']) ? true : false;
		
	
	$showchecks = (@$config['be_seo_showchecks'] == 'checked' || $showtests) ? true : false;
	$css_detailsonly = ($showchecks) ? '' : 'seocu-detailsonly ';
	
		
	/*
	$regex_script = "/<script((?!src=)[^>])*?>.*?<\/script>/is";	//regex: Script-Bereiche --> kein U-Modifier, da bereits non-greedy
	$regex_style = "/<style[^>]*?>.*?<\/style>/is";					//regex: Style-Bereiche --> kein U-Modifier, da bereits non-greedy
	*/
	$regex_wspace = "/\s\s+/";										//regex: Multiple Leerzeichen
	$regex_pmarks = "/[[:punct:]]/";								//regex: Satzzeichen + gängige Sonderzeichen
	
	
    //SEO+HTML-Daten vorbereiten
	$yr = array();
	$prot = 'http://';																								//TODO: Protokoll aus Aufruf bereits auslesen, falls kein yRewrite genutzt wird
	$dom = $_SERVER['SERVER_NAME'];
	$url = $prot.$_SERVER['SERVER_NAME'].rex_getUrl($actArt, $actClang);

		//mit Daten aus yRewrite abgleichen
		if (rex_addon::get('yrewrite')->isAvailable()):
			$yrs = new rex_yrewrite_seo();
				$yr['title'] = $yrs->getTitle();
				$yr['titletag'] = $yrs->getTitleTag();
				$yr['desc'] = $yrs->getDescription();
				$yr['desctag'] = $yrs->getDescriptionTag();
					$tmp = $yrs->getRobotsTag();
				$yr['robots'] = (!empty($tmp)) ? preg_replace('/^.*content\s*=\s*"(.*)".*/i', "$1", $tmp) : '';
				$yr['canonical'] = $yrs->getCanonicalUrlTag();
			
			$prot = (rex_yrewrite::isHttps()) ? 'https://' : $prot;
			$dom = rex_yrewrite::getDomainByArticleId($actArt)->getName();
			$url = rex_yrewrite::getFullUrlByArticleId($actArt, $actClang);
		endif;
		
	
		//Daten des Artikels holen 
		rex::setProperty('redaxo', false);
		$html = $httpheader = $sockerror = $hasRedirect = $artcnt = $artcnt_raw = $artcnt_wo_h1 = "";	
		
		
		//Live-Artikel holen & auf aktive Redirects testen
		$htmlloaded = false;
		try {
			$sock = rex_socket::factoryUrl($url);
			$resp = $sock->doGet();
			
			$httpheader = $resp->getHeader();				
			if ($resp->isOk()):
				$htmlloaded = true;
				$html = $resp->getBody();
				$html = trim($html);
			endif;
		} catch(rex_socket_exception $e) { $sockerror = $e->getMessage(); }
		
		$httpheader = (is_array($httpheader)) ? implode("|", $httpheader) : str_replace(array("\r\n", "\n"), "|", $httpheader);
		$hasRedirect = (preg_match("/http[\/0-9\.]* 30[0-9]{1,1}/i", $httpheader) && mb_stripos($httpheader, "|location:") !== false) ? true : false;
		unset($httpheader);
		
		
		//Hyphenator-Addon anbinden
		$hyph = rex_addon::get('hyphenator');
		if ($config['be_seo_hyphenator'] && $hyph->isAvailable()):
			$hyphen = $hyph->getConfig('hyphen');
				$hyphen = (empty($hyphen)) ? '&shy;' : $hyphen;
				
			$html = preg_replace("/".$hyphen."/im", "", $html);
		endif;
		
		
		//Redaxo-Artikel bzw. Content holen
		if (!$hasRedirect):
			$artcnt_raw = (empty($artcnt_raw)) ? preg_replace("/^[\s\S]*<body[^\>]*>([\s\S]*)<\/body>[\s\S]*$/im", "$1", $html) : $artcnt_raw;				
		endif;
		rex::setProperty('redaxo', true);
		
		
		//Content aufbereiten
		$artcnt_raw = trim($artcnt_raw);
			$artcnt_raw = ($config['be_seo_removeblock_header']) 	? preg_replace("/<header[^\>]*>.*<\/header>/imsU", "", $artcnt_raw) : $artcnt_raw;			//--> U-Modifier, da sonst greedy
			$artcnt_raw = ($config['be_seo_removeblock_footer']) 	? preg_replace("/<footer[^\>]*>.*<\/footer>/imsU", "", $artcnt_raw) : $artcnt_raw;			//--> U-Modifier, da sonst greedy
			$artcnt_raw = ($config['be_seo_removeblock_nav']) 		? preg_replace("/<nav[^\>]*>.*<\/nav>/imsU", "", $artcnt_raw) : $artcnt_raw;				//--> U-Modifier, da sonst greedy
		$arthead = trim(mb_substr($html, 0, mb_stripos($html, "</head>")));
		unset($html);
		
        $artcnt = $artcnt_raw;
            $artcnt = a1544_removeScript($artcnt);																							//Script-Blöcke entfernen
            $artcnt = a1544_removeStyle($artcnt);																							//Style-Blöcke entfernen
			$artcnt = a1544_removeEntities($artcnt);
			
		$artcnt_tags = $artcnt;
			$artcnt = trim(preg_replace($regex_wspace, " ", a1544_removeTags($artcnt)));													//Tags entfernen + mehrfache Leerzeichen vereinfachen
			$artcnt_tags = trim(preg_replace($regex_wspace, " ", $artcnt_tags));																//mehrfache Leerzeichen vereinfachen
		$artcnt_wo_h1 = preg_replace("/<h1[^>]*>.*?<\/h1>/is", "", $artcnt_raw);															//Content ohne H1-Überschriften --> kein U-Modifier, da bereits non-greedy
			$artcnt_wo_h1 = trim(preg_replace($regex_wspace, " ", a1544_removeSST($artcnt_wo_h1)));											//Script, Style, Tags entfernen, mehrfache Leerzeichen vereinfachen		
		
		$art = rex_article::get($actArt, $actClang);																						//Artikel referenzieren für weitere Prüfungen
		
		//dump("artcnt_raw\n\n\n".$artcnt_raw);
		//dump("artcnt\n\n\n".$artcnt);
		//dump("artcnt_wo_h1\n\n\n".$artcnt_wo_h1);

	
    //Prüfungen durchführen
	$col_ok = "rex-online";
	$col_nok = "rex-offline";
    $icon_ok = "fa-check-circle rex-online";
    $icon_nok = "fa-times-circle rex-offline";
    $icon_info = "fa-info-circle";
    $icon_att = "fa-exclamation-circle";
	
	$checks = $checks_ok = 0;
	$css = "";
	$css_sub = ($showchecks) ? 'seocu-indent' : '';
	
    
    $cnt .= '<div class="seocu-check">';
	
		//Hinweis auf Einlesen nicht möglich
		$cnt .= (!empty($sockerror)) ? '<span class="seocu-head">'.str_replace("###error###", $sockerror, rex_i18n::rawmsg('a1544_seo_nohtml')).'</span>' : '';	
		
		//Hinweis auf aktive Weiterleitung im Artikel
		$cnt .= ($hasRedirect) ? '<span class="seocu-head">'.rex_i18n::rawmsg('a1544_seo_artredirect').'</span>' : '';
	
		//Hinweis auf offline-Artikel
		$cnt .= ($htmlloaded && !$art->isOnline()) ? '<span class="seocu-head">'.rex_i18n::rawmsg('a1544_seo_artoffline').'</span>' : '';
		
		//Hinweis auf geänderten Artikel
		$artChanged = ($art->isOnline() && preg_match("/(seocucnt=changed|bloecks=status)/i", $lasturl)) ? true : false;
		$cnt .= ($artChanged) ? '<span class="seocu-head">'.rex_i18n::rawmsg('a1544_seo_artchanged').'</span>' : '';
				
	
		//Einleitung
    	$cnt .= '<span class="seocu-head seocu-first-head">'.rex_i18n::msg('a1544_seo_tests').'</span>';																	
			//Hinweis auf fehlende Auswahl von Prüfungen
			$cnt .= ($is_nochecks) ? '<span class="seocu-head" style="margin-top: 0px;">'.rex_i18n::rawmsg('a1544_seo_nochecksselected').'</span>' : '';
        $cnt .= '<ul>';
		$cnt .= '###detaillink###';
		
        
		//Einzelwerte aufbereiten
		preg_match("/<title[^>]*>(.*?)<\/title>/is", $arthead, $matches);																										//Title holen --> kein U-Modifier, da bereits non-greedy
			$title = (!isset($matches[1]) || empty($matches[1])) ? $yrs->getTitle() : $matches[1];
			$title = trim(preg_replace("/\s\s+/", " ", $title));
			$title_raw = aFM_unmaskQuotes(aFM_revChar($title));
			//$title_words = (!empty($title_raw)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", $title_raw))) ) : array();				//Wörter in title finden (alt)
			$title_words = (!empty($title_raw)) ? a1544_countWords( trim(preg_replace($regex_wspace, " ", $title_raw)), 'array') : array();										//Wörter in title finden
			
			
		preg_match("/<meta name\s*=\s*[\"']{1}description[\"']{1}[ ]+content\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>/is", $arthead, $matches);									//Description holen
			$desc = (!isset($matches[1]) || empty($matches[1])) ? $yrs->getDescription() : $matches[1];
			$desc = trim(preg_replace("/\s\s+/", " ", $desc));
			$desc_raw = aFM_unmaskQuotes(aFM_revChar($desc));


		preg_match_all("/<h1[^>]*>(.*?)<\/h1[^>]*>/is", $artcnt_raw, $matches);																									//H1-Überschrift(en) holen --> kein U-Modifier, da bereits non-greedy
			$h1 = $h1cnt = (isset($matches[1])) ? $matches[1] : '';
			$h1cnt = (is_array($h1cnt)) ? implode(" ", $h1cnt) : $h1cnt;
				$h1cnt = trim(preg_replace($regex_wspace, " ", a1544_removeSST($h1cnt)));																						//Script, Style, Tags entfernen
			//$h1_words = (!empty($h1cnt)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", aFM_unmaskQuotes($h1cnt)))) ) : array();		//Wörter in H1 finden (alt)
			$h1_words = (!empty($h1cnt)) ? a1544_countWords( trim(preg_replace($regex_wspace, " ", aFM_unmaskQuotes($h1cnt))), 'array') : array();								//Wörter in H1 finden


		preg_match_all("/<h2[^>]*>(.*?)<\/h2[^>]*>/is", $artcnt_raw, $matches);																									//H2-Überschrift(en) holen --> kein U-Modifier, da bereits non-greedy
			$h2 = $h2cnt = (isset($matches[1])) ? $matches[1] : '';
			$h2cnt = (is_array($h2cnt)) ? implode(" ", $h2cnt) : $h2cnt;
				$h2cnt = trim(preg_replace($regex_wspace, " ", a1544_removeSST($h2cnt)));																						//Script, Style, Tags entfernen
			//$h2_words = (!empty($h2cnt)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", aFM_unmaskQuotes($h2cnt)))) ) : array();		//Wörter in H2 finden (alt)
			$h2_words = (!empty($h2cnt)) ? a1544_countWords( trim(preg_replace($regex_wspace, " ", aFM_unmaskQuotes($h2cnt))), 'array') : array();								//Wörter in H2 finden


		preg_match_all("/<h([1-6]{1})[^>]*>(.*?)<\/h[1-6]{1}[^>]*>/is", $artcnt_raw, $matches);																					//alle Überschriften holen --> kein U-Modifier, da bereits non-greedy
			$hx = (isset($matches[2])) ? $matches[2] : '';
			$hx_types = (isset($matches[1])) ? $matches[1] : '';


		$has_og = (float)preg_match_all("/<meta property\s*=\s*[\"']{1}og:[^\"']*[\"']{1}[ ]+content\s*=\s*[\"']{1}[^\"']*[\"']{1}[^>]*>/iU", $arthead);						//Anzahl OG-Tags holen --> U-Modifier, da sonst greedy
			$has_og = ($has_og > 0) ? true : false;
			preg_match("/<meta property\s*=\s*[\"']{1}og:title[\"']{1}[ ]+content\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>/is", $arthead, $matches);
				$ogtitle = (isset($matches[1])) ? trim(preg_replace("/\s\s+/", " ", $matches[1])) : '';
				$ogtitle_raw = aFM_unmaskQuotes($ogtitle);
			preg_match("/<meta property\s*=\s*[\"']{1}og:description[\"']{1}[ ]+content\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>/is", $arthead, $matches);
				$ogdesc = (isset($matches[1])) ? trim(preg_replace("/\s\s+/", " ", $matches[1])) : '';
				$ogdesc_raw = aFM_unmaskQuotes($ogdesc);
			preg_match("/<meta property\s*=\s*[\"']{1}og:url[\"']{1}[ ]+content\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>/is", $arthead, $matches);
				$ogurl = (isset($matches[1])) ? trim(preg_replace("/\s\s+/", " ", $matches[1])) : '';
				$ogurl_raw = aFM_unmaskQuotes($ogurl);


		//$content_words = (!empty($artcnt)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", $artcnt))) ) : array();						//Wörter im Content finden (alt)
		$content_words = (!empty($artcnt)) ? a1544_countWords( trim(preg_replace($regex_wspace, " ", $artcnt)), 'array') : array();												//Wörter im Content finden	


		preg_match_all("/<(b\b|strong\b)[^>]*>(.*)<\/(b|strong)>/isU", $artcnt_tags, $matches);																					//alle strong-tags holen (<strong>) --> U-Modifier, da sonst greedy
			$bolds = (isset($matches[2])) ? $matches[2] : array();																												// \b = Nichtwortzeichen als Abgrenzung


		preg_match_all("/<img([\w\W]+?)\/>/is", $artcnt_tags, $matches);																										//alle Bilder holen --> kein U-Modifier, da bereits non-greedy
			$imgs = (isset($matches[1])) ? $matches[1] : array();																												//alt: /<img [^\/>]*\/>/isU
			$imgcnt = (is_array($imgs)) ? implode(" ", $imgs) : $imgs;


		preg_match_all("/<a [^>]*href\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>(.*)<\/a>/isU", $artcnt_tags, $matches);																//alle Verlinkungen holen (a href) --> U-Modifier, da sonst greedy
			$links = (isset($matches[1])) ? $matches[1] : array();
			$linknames = (isset($matches[2])) ? $matches[2] : array();
		
		
		// ----------------------------------------------------------------------------------------------------------------------------------- //
			
			
		//Prüfungen (title, desc, opengraph (title, desc, url), h1, content)
		//title & desc
		if ($config['be_seo_checks_titledesc']):
			//title
			if (!empty($title)):
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_title_ok').'</li>';
				$checks_ok++;
				
				if (mb_strlen(utf8_decode($title_raw)) < $config['be_seo_title_min']):
					$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_title_min'], $config['be_seo_title_max']), rex_i18n::rawmsg('a1544_seo_title_short')).'</li>';
				elseif (mb_strlen(utf8_decode($title_raw)) > $config['be_seo_title_max']):
					$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_title_min'], $config['be_seo_title_max']), rex_i18n::rawmsg('a1544_seo_title_long')).'</li>';
				else:
					$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_title_opt').'</li>';
					$checks_ok++;
				endif;
				$checks++;
				
				$cnt .= (count($title_words) >= $config['be_seo_title_words']) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_title_words').'</li>' : '';
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_title_nok').'</li>';
			endif;
			$checks++;
		

			//desc
			if (!empty($desc)):
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_desc_ok').'</li>';
				$checks_ok++;
				
				if (mb_strlen(utf8_decode($desc_raw)) < $config['be_seo_desc_min']):
					$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_desc_min'], $config['be_seo_desc_max']), rex_i18n::rawmsg('a1544_seo_desc_short')).'</li>';
				elseif (mb_strlen(utf8_decode($desc_raw)) > $config['be_seo_desc_max']):
					$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_desc_min'], $config['be_seo_desc_max']), rex_i18n::rawmsg('a1544_seo_desc_long')).'</li>';
				else:
					$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_desc_opt').'</li>';
					$checks_ok++;
				endif;
				$checks++;
				
				//$cnt .= (count(explode(" ", $desc)) >= $config['be_seo_desc_words']) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_desc_words').'</li>' : '';		 	//(alt)
				$cnt .= (a1544_countWords($desc) >= $config['be_seo_desc_words']) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_desc_words').'</li>' : '';
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_desc_nok').'</li>';
			endif;
			$checks++;
		endif;
				
		
		//opengraph (aus SEO-Sicht optional, da vorwiegend für SocialMedia)
		if ($has_og && $config['be_seo_checks_opengraph']):
			$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_og_ok').'</li>';
			
			if (!empty($ogtitle)):
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogtitle_ok').'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogtitle_nok').'</li>';
			endif;
			$checks++;

			if (!empty($ogdesc)):
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogdesc_ok').'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogdesc_nok').'</li>';
			endif;
			$checks++;

			if (!empty($ogurl)):
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogurl_ok').'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogurl_nok').'</li>';
			endif;
			$checks++;
		endif;
				
		
		//URL
		if ($config['be_seo_checks_url']):
			//URL Länge		
			$tmp = preg_replace("/(http[s]?:\/\/|\/$)/i", "", str_replace($dom, "", $url));
				$tmp = preg_replace("/^\//i", "", $tmp);
			if (mb_strlen(utf8_decode($tmp)) > $config['be_seo_url_max']):
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###max###"), array($config['be_seo_url_max']), rex_i18n::rawmsg('a1544_seo_url_length_nok')).'</li>';
			else:
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_url_length_ok').'</li>';
				$checks_ok++;
			endif;
			$checks++;
			
			
			//URL Verzeichnistiefe
			$tmp = explode("/", $tmp);
			if (count($tmp) > $config['be_seo_url_depths']):
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###max###"), array($config['be_seo_url_depths']), rex_i18n::rawmsg('a1544_seo_url_depth_nok')).'</li>';
			else:
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_url_depth_ok').'</li>';
				$checks_ok++;
			endif;
			$checks++;		
		endif;
				
		
		//Header
		if ($config['be_seo_checks_header']):
			//H1
			if (count($h1) > 1):
				//mehrere H1 gefunden
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_h1multi').'</li>';
			elseif (empty($h1[0])):
				//keine H1 gefunden
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_h1_nok').'</li>';
			else:
				//genau 1 H1 gefunden
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_h1_ok').'</li>';
				
				
				$cnt .= (count($h1_words) < 2 || mb_strlen($h1cnt) < 20) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_h1_short').'</li>' : '';			//Hinweis: H1 ist zu kurz oder nur 1 Wort
				
				
				$checks_ok++;
			endif;
			$checks++;
			
			
			//H-Struktur
			$hxcount = count($hx_types);
			if ($hxcount > 0):
				$hxlast = $hx_types[0];
				$hxerror = ($hxlast != 1) ? true : false;
	
				$tmp = ($hxerror) ? $col_nok : '';
				$hxlist = '<div id="seocu-hxlist" class="seocu-infolist seocu-hide">';
				$hxlist .= '<dl><dt>H'.$hx_types[0].'</dt><dd class="'.$tmp.'">'.$hx[0].'</dd></dl>';
				for ($i=1; $i < $hxcount; $i++):
					if ($hx_types[$i] != 1 && ($hx_types[$i] == $hxlast || $hx_types[$i] < $hxlast || $hx_types[$i] == ($hxlast+1))):
						$tmp = "";
					else:
						$hxerror = true;
						$tmp = $col_nok;
					endif;
					
					$hxlast = $hx_types[$i];					
					$hxlist .= '<dl><dt>H'.$hx_types[$i].'</dt><dd class="'.$tmp.'">'.$hx[$i].'</dd></dl>';
				endfor;
				$hxlist .= '</div>';
				
				if (!$hxerror):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_hx_ok').'</li>';
					$cnt .= ($hxcount < 2) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_hx_short').'</li>' : '';			//Empfehlung: mehr als 1 Überschrift
					$checks_ok++;
				else:
					$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i><span class="seocu-infolistswitch" data-seocu-dst="#seocu-hxlist">'.rex_i18n::msg('a1544_seo_hx_nok').'&nbsp;<span class="rex-icon fa-caret-down"></span></span>'.$hxlist.'</li>';
				endif;
				$checks++;
			endif;
			unset($hxlist, $hxerror);
		endif;
				
		
		//Content
		//$wcount = (!empty($artcnt)) ? count(explode(" ", $artcnt)) : 0;		 	//(alt)
		$wcount = (!empty($artcnt)) ? a1544_countWords($artcnt) : 0;
		if ($config['be_seo_checks_content']):
			//Content-Länge
			if ($wcount >= $config['be_seo_content_words']):
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###words###", $wcount, rex_i18n::msg('a1544_seo_cnt_ok')).'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###words###", "###cwords###"), array($wcount, $config['be_seo_content_words']), rex_i18n::msg('a1544_seo_cnt_short')).'</li>';
			endif;
			$checks++;
			
			
			//content -> gefundene title Wörter
			$found = 0;
			foreach ($title_words as $word):
				if (mb_stristr($artcnt_wo_h1, $word)) { $found++; }
			endforeach;
			
			if (count($title_words) == $found):
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_titlewords_ok').'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_titlewords_nok').'</li>';
			endif;
			$checks++;
			
			
			//content -> gefundene H1 Wörter
			$found = 0;
			foreach ($h1_words as $word):
				if (mb_stristr($artcnt_wo_h1, $word)) { $found++; }
			endforeach;
			
			if (count($h1_words) == $found):
				$cnt .= '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_h1words_ok').'</li>';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_h1words_nok').'</li>';
			endif;
			$checks++;
			
			
			//bold-strong Tags -> Länge oder leer
			$bcnt = "";
			$berror = $bcount = $bempty = 0;
			if (count($bolds) > 0):
				$boldlist = '<div id="seocu-boldlist" class="seocu-infolist seocu-hide">';
				
				//Anzahl berechnen
				$curbolds = count($bolds);
				$maxbolds = ($wcount > 300) ? ceil($wcount / 54) : 6;
				
				//Inhalt prüfen
				foreach ($bolds as $bold):
					$bold = trim($bold);
					if (empty($bold)) { $bempty++; }
	
					$tmp = mb_strlen(utf8_decode($bold));
					if ($tmp > 70):
						$bcount++;
						$boldlist .= '<dl><dt>'.$tmp.' '.rex_i18n::msg('a1544_seo_bolds_char').'</dt><dd class="'.$col_nok.'">'.$bold.'</dd></dl>';
					endif;
				endforeach;
				$boldlist .= '</div>';
				
				//Info: Anzahl
				if (count($bolds) > $maxbolds):
					$berror = 1;
					$bcnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###count###", "###max###"), array($curbolds, $maxbolds), rex_i18n::rawmsg('a1544_seo_bolds_long')).'</li>';
				else:
					$bcnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_bolds_opt').'</li>';
				endif;
				
				//Info: leere Tags
				if ($bempty > 0):
					$berror = 1;
					$bcnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_bolds_empty').'</li>';
				endif;
				
				//Info: Tags zu lang + Übersicht
				if ($bcount > 0):
					$berror = 1;
					$bcnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i><span class="seocu-infolistswitch" data-seocu-dst="#seocu-boldlist">'.str_replace("###count###", $bcount, rex_i18n::rawmsg('a1544_seo_bolds_length')).'&nbsp;<span class="rex-icon fa-caret-down"></span></span>'.$boldlist.'</li>';
				endif;
			endif;
			unset($boldlist);
			
			if ($berror):
				$cnt .= $bcnt;
			else:
				$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_bolds_ok').'</li>';											//Keine probleme mit bold-Tags gefunden
				$cnt .= (count($bolds) <= 0) ? '<li class="'.$css_detailsonly.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_bolds_short').'</li>' : '';			//Empfehlung: bold-Tags nutzen
				$checks_ok++;
			endif;
			$checks++;
		endif;
		
		
		//images
		if ($config['be_seo_checks_images']):
			$acount = 0;
			if (count($imgs) > 0):
				$checks_ok++;
				
				$imglist = '<div id="seocu-imglist" class="seocu-infolist seocu-hide">';
				foreach ($imgs as $img):
					preg_match("/alt\s*=\s*[\"']{1}([^\"']*)[\"']{1}/i", $img, $matches);
					if (!isset($matches[1]) || empty($matches[1])):
						$acount++;
						
						preg_match("/src\s*=\s*[\"']{1}([^\"']*)[\"']{1}/i", $img, $matches);
						$imglist .= (isset($matches[1]) && !empty($matches[1])) ? '<dl><dt>IMG</dt><dd class="'.$col_nok.'">'.$matches[1].'</dd></dl>' : '';
					endif;
				endforeach;
				$imglist .= '</div>';
				
				if ($acount > 0):
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i><span class="seocu-infolistswitch" data-seocu-dst="#seocu-imglist">'.str_replace("###count###", $acount, rex_i18n::msg('a1544_seo_img_alt_nok')).'&nbsp;<span class="rex-icon fa-caret-down"></span></span>'.$imglist.'</li>';
				else:
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_img_ok').'</li>';
					$checks_ok++;
				endif;
				$checks++;
				
				unset($imglist);
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_img_nok').'</li>';
			endif;
			$checks++;
		endif;
		
		
		//Links
		if ($config['be_seo_checks_links']):
			//Anzahl Links
			$lcount = $lcount_int = $lcount_ext = $lcount_intimg = 0;
			if (count($links) > 0):
				$checks_ok++;
				
				foreach ($links as $link):
					$link = trim($link);
					
					if (!empty($link)):
						if (preg_match("/^#/i", $link)): continue; endif;
	
						$lcount++;
						if (mb_stristr($link, $dom) || !preg_match("/^(http[s]?:\/\/)/i", $link)):
							if (preg_match("/\.(jpg|jpeg|gif|png|svg|webp)$/i", $link)):
								$lcount_intimg++;
							else:
								$lcount_int++;
							endif;
						else:
							$lcount_ext++;
						endif;
					endif;
				endforeach;
				
				if ($lcount_int < $config['be_seo_links']):
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###intlinks###", "###extlinks###"), array($lcount_int, $lcount_ext), rex_i18n::msg('a1544_seo_links_int_nok')).'</li>';
				else:
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.str_replace(array("###intlinks###", "###extlinks###"), array($lcount_int, $lcount_ext), rex_i18n::msg('a1544_seo_links_ok')).'</li>';
					$checks_ok++;
				endif;
				$checks++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_links_nok').'</li>';
			endif;
			$checks++;
						
			//Broken Links (extern/intern) -> auf Code 200 prüfen
			//später evtl.
		endif;
		
		
		// ----------------------------------------------------------------------------------------------------------------------------------- //
			
	
		//Keyword-Prüfungen (title, desc, url, opengraph, h1, density, unique, content, ...)
		if (!empty($keyword)):
			//$cnt .= ($showchecks || $checks > $checks_ok) ? '<li>&nbsp;</li>' : '';
			$cnt .= ($checks <= $checks_ok) ? '<li class="'.$css_detailsonly.'">&nbsp;</li>' : '';
			$cnt .= ($checks > $checks_ok) ? '<li>&nbsp;</li>' : '';
		
			//title & desc
			if ($config['be_seo_checks_titledesc']):
				//title
				if (mb_stristr($title_raw, $keyword)):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keytitle_ok').'</li>';
					$checks_ok++;
					
					//Keyword ist max. das 3. Wort (definierbar)
					if (count($title_words) > 0):
						$found = false;
						for ($w=0; $w<3; $w++):
							if (mb_strtolower($title_words[$w]) == $keyword) { $found = true; }
						endfor;
						
						if ($found):
							$checks_ok++;
						else:
							$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keytitle_atstart_nok').'</li>';
						endif;
						$checks++;
					endif;
					
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keytitle_nok').'</li>';
				endif;
				$checks++;
				
				
				//desc
				if (mb_stristr($desc_raw, $keyword)):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keydesc_ok').'</li>';
					$checks_ok++;
					
					//Keyword in den ersten 120 Zeichen gefunden (definierbar)
					if (mb_stristr(mb_substr($desc_raw, 0,120), $keyword)):
						$checks_ok++;
					else:
						$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keydesc_atstart_nok').'</li>';
					endif;
					$checks++;
					
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keydesc_nok').'</li>';
				endif;
				$checks++;
			endif;
			
			
			//Header
			if ($config['be_seo_checks_header']):
				//H1
				if (mb_stristr($h1cnt, $keyword)):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyh1_ok').'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyh1_nok').'</li>';
				endif;
				$checks++;			
				
				
				//H1-H6
				if (count($hx) > 0):
					$found = 0;
					foreach ($hx as $h):
						if (mb_stristr($h, $keyword)) { $found++; }
					endforeach;
					
					if ($found >= 2):
						$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###count###", $found, rex_i18n::msg('a1544_seo_keyhx_ok')).'</li>';
						$checks_ok++;
					else:
						$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyhx_nok').'</li>';
					endif;
					$checks++;
				endif;
			endif;
			
			
			//Content
			$kcountbody = (float)preg_match_all("/".$keyword."/iU", $artcnt);				//U-Modifier, da sonst greedy
			if ($config['be_seo_checks_content']):
				//content (Länge)
				if ($kcountbody > 0):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###count###", $kcountbody, rex_i18n::msg('a1544_seo_keycnt_ok')).'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keycnt_nok').'</li>';
				endif;
				$checks++;
	
				
				//content (Keyword am Anfang)
				if (count($content_words) > 0):
					$found = false;
					for ($w=0; $w<400; $w++):
						if (mb_strtolower($content_words[$w]) == $keyword) { $found = true; }
					endfor;
					
					if ($found):
						$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keycnt_atstart_ok').'</li>';
						$checks_ok++;
					else:
						$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keycnt_atstart_nok').'</li>';
					endif;
					
					$checks++;
				endif;							
					
				
				//Keyword in bold-Tags vorhanden, wenn bold-Tags vorhanden sind
			
			endif;
			
			
			//URL
			if ($config['be_seo_checks_url']):
				if (!mb_stristr($url, $keyword)):
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_nok').'</li>';
				elseif (mb_substr_count($url, $keyword) > 1):
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_multi').'</li>';
				else:
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_ok').'</li>';
					$checks_ok++;
				endif;
				$checks++;
			endif;
						
			
			//Images (alt, title, src)
			if (count($imgs) > 0 && $config['be_seo_checks_images']):
				$found_alt = $found_title = $found_src = 0;
				foreach ($imgs as $img):
					preg_match("/alt\s*=\s*[\"']{1}([^\"']*)[\"']{1}/i", $img, $matches);
					if (isset($matches[1]) && mb_stristr($matches[1], $keyword)) { $found_alt++; }
						
					preg_match("/title\s*=\s*[\"']{1}([^\"']*)[\"']{1}/i", $img, $matches);
					if (isset($matches[1]) && mb_stristr($matches[1], $keyword)) { $found_title++; }

					preg_match("/src\s*=\s*[\"']{1}([^\"']*)[\"']{1}/i", $img, $matches);
					if (isset($matches[1]) && mb_stristr($matches[1], $keyword)) { $found_src++; }
				endforeach;
				
				//ALT
				if ($found_alt > 0):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_alt_ok').'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_alt_nok').'</li>';
				endif;
				$checks++;

				//TITLE
				if ($found_title > 0):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_title_ok').'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_title_nok').'</li>';
				endif;
				$checks++;
				
				//SRC
				if ($found_src > 0):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_src_ok').'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_src_nok').'</li>';
				endif;
				$checks++;
			endif;			
				
					
			//density
			if ($config['be_seo_checks_density']):
				$tmp = ($wcount > 0) ? round( (float)($kcountbody / $wcount) * 100, 2) : 0;
					$l = str_replace(array("###min###", "###max###", "###density###"), array($config['be_seo_density_min'], $config['be_seo_density_max'], $tmp), rex_i18n::rawmsg('a1544_seo_density_nok'));
					
				if ($tmp >= $config['be_seo_density_min'] && $tmp <= $config['be_seo_density_max']):
					$cnt .= '<li class="'.$css_detailsonly.'"><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###density###", $tmp, rex_i18n::msg('a1544_seo_density_ok')).'</li>';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.$l.'</li>';
				endif;
				$checks++;
			endif;
			
				
			$cnt .= '<li>&nbsp;</li>';
			
			
			//Info: unique or multi keyword (Abfrage aus DB)
			$artdom = rex_yrewrite::getDomainByArticleId($actArt);
			$artdommp = intval($artdom->getMountId());
				$path = ($artdommp > 0) ? "%|$artdommp|%" : "|%";

			$db = rex_sql::factory();
				$offkeys = ($config['be_seo_offlinekeywords']) ? "" : " AND status = '1'";
			$db->setQuery("SELECT id FROM ".rex::getTable('article')." WHERE seocu_keyword = '".aFM_maskSql($keyword)."' AND path LIKE '".$path."'".$offkeys);
				$tmp = ($db->getRows() <= 1) ? rex_i18n::msg('a1544_seo_keyunique') : rex_i18n::msg('a1544_seo_keymulti');
			$cnt .= '<li><i class="rex-icon '.$icon_info.'"></i>'.$tmp.'</li>';
			unset($db);
		endif;
		
		
		/*	Bilder sind aus SEO-Sicht wichtig -> Prüfung oben daher jetzt als Pflicht
		//Info: keine Bilder vorhanden
		$cnt .= (count($imgs) <= 0 && $config['be_seo_checks_images']) ? '<li><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_img_notfound').'</li>' : '';
		*/
		
		
		//Line-Spacer
		$cnt .= (empty($keyword) && $config['be_seo_checks_flesch']) ? '<li>&nbsp;</li>' : '';
		
		
		//Flesch-Index
		if ($config['be_seo_checks_flesch']):
			$tmp = $artcnt;
				$tmp = preg_replace("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4})/i", "$1-$2-$3", $tmp);						//deutsches Datum ersetzen, um Sätze bessere zählen zu können
			$sents = (float)preg_match_all("/([^\.\!\?]+[\.\?\!]*)/", $tmp);												//Sätze anhand der üblichen Satzzeichen trennen
			$sylls = 0;																										//Silben anhand der Vokale zählen
				for ($i=0; $i < mb_strlen($artcnt); $i++):
					if (preg_match("/[aeiouyäöü]/i", $artcnt[$i]) && !preg_match("/[aeiouyäöü]/i", $artcnt[$i-1])) { $sylls++; }
				endfor;
			
			$asl = ($sents > 0) ? (float)($wcount / $sents) : 0;
			$asw = ($wcount > 0) ? (float)($sylls / $wcount) : 0;
			
			//echo "ASL: $asl / ASW: $asw\n";
			
			$flesch_score = (preg_match("/(de|dede|de-de|de_de|deu|ger|deutsch|german)/i", rex_clang::get($actClang)->getCode())) ? round((180 - $asl - (58.5 * $asw)), 1) : round((206.835 - (1.015 * $asl) - (84.6 * $asw)), 1);			//deutsch / englisch
				$flesch_score = ($flesch_score <= 0 || empty($artcnt)) ? 0 : $flesch_score;
				$flesch_score = ($flesch_score >= 100) ? 100 : $flesch_score;
				
			//echo "FSCORE: $flesch_score\n";
			
			$flesch_result = rex_i18n::msg('a1544_seo_flesch_grade1');
				$flesch_result = ($flesch_score >= 75 && $flesch_score < 90) ? rex_i18n::msg('a1544_seo_flesch_grade2') : $flesch_result;
				$flesch_result = ($flesch_score >= 55 && $flesch_score < 75) ? rex_i18n::msg('a1544_seo_flesch_grade3') : $flesch_result;
				$flesch_result = ($flesch_score >= 30 && $flesch_score < 55) ? rex_i18n::msg('a1544_seo_flesch_grade4') : $flesch_result;
				$flesch_result = ($flesch_score < 30) ? rex_i18n::msg('a1544_seo_flesch_grade5') : $flesch_result;
			
			$cnt .= '<li><i class="rex-icon '.$icon_info.'"></i>'.str_replace(array("###score###", "###result###"), array($flesch_score, $flesch_result), rex_i18n::rawmsg('a1544_seo_flesch')).'</li>';
		endif;
		
		
		// --- ENDE : Prüfungen --- //
		
		
        $cnt .= '</ul>';
    $cnt .= '</div>';


	//Basis-Analyse zwischenspeichern für Cache-Ausgaben
	$analysis = $cnt;
		

	//Ausgabe WDF-Tabelle
	if ($config['be_seo_checks_wdf']):
		$wdf = a1544_seocuWDF($artcnt);
		
		//Ausgabe in Sidebar & in Details
		if ($showchecks || $config['be_seo_sidebar_wdf']):
			$wdflist = "";
			if (is_array($wdf) && count($wdf) > 0):
				$wdflist .= '<div class="seocu-wdftable">';
				$wdflist .= '<span class="seocu-head">'.rex_i18n::msg('a1544_bas_list_wdf').':###wdflink###</span>';
			
				$wdflist .= '<table border="0" cellpadding="0" cellspacing="0"><tr>';
					$wdflist .= '<th>'.rex_i18n::msg('a1544_seo_wdf_table_keyword').'</th>';
					$wdflist .= '<th>'.rex_i18n::msg('a1544_seo_wdf_table_count').'</th>';
					$wdflist .= '<th title="'.rex_i18n::msg('a1544_seo_wdf_table_wdf_info').'">'.rex_i18n::msg('a1544_seo_wdf_table_wdf').'</th>';
					$wdflist .= '<th title="'.rex_i18n::msg('a1544_seo_wdf_table_density_info').'">'.rex_i18n::msg('a1544_seo_wdf_table_density').'</th>';
					$wdflist .= '</tr>';
			
				$w=0;
				foreach ($wdf as $key=>$val):
                	if (preg_match("/\./", $val['wdf'])):
                    	$val['wdf'] = explode(".", $val['wdf']);
                        $val['wdf'] = $val['wdf'][0].".".mb_substr($val['wdf'][1], 0,2);
                    endif;
                    
					$wdflist .= '<tr>';
						$wdflist .= '<td>'.$key.'</td>';
						$wdflist .= '<td>'.$val['count'].'</td>';
						$wdflist .= '<td>'.$val['wdf'].'</td>';
						$wdflist .= '<td>'.$val['kd'].'</td>';
					$wdflist .= '</tr>';
					
					$w++;
					if ($w == 5) { break; }
				endforeach;
				
				$wdflist .= '</table>';
				$wdflist .= '</div>';
				
				$cnt .= $wdflist;
			endif;
		endif;
	endif;
			
	
	
	//Testausgaben : START (FALKO)
	/*
	echo "Wörter Title: ".count($title_words)."<br>";
	echo "Wörter H1: ".count($h1_words)."<br>";
	
	$tmp = a1544_countWords($artcnt, 'array');
	echo "Wörter Content (old): ".$wcount."<br>";
	echo "Wörter Content: ".count($tmp)."<br>";
	dump($title_words);
	dump($h1_words);
	dump($tmp);
	
	exit();
	*/
	//Testausgaben : ENDE
	
	
	
	//Resultat aufbereiten
	$result = ($checks > 0) ? round( (float)($checks_ok * 100 / $checks), 0) : 0;
	$resultcol = "#3BB594";
		$resultcol = ($result > 70 && $result < 90) ? "#CEB964" : $resultcol;
		$resultcol = ($result >= 50 && $result <= 70) ? "#F90" : $resultcol;
		$resultcol = ($result > 30 && $result < 50) ? "#EC7627" : $resultcol;
		$resultcol = ($result <= 30) ? "#D9534F" : $resultcol;
	$quick = ($artChanged) ? '<span class="info"><i class="rex-icon fa-exclamation-triangle"></i></span>' : '<span>'.$result.'%</span>';
	$cnt .= '<script type="text/javascript">$(function(){ seocuqi = $(".seocu-quickinfo"); seocuqi.css({ color: "'.$resultcol.'"}); if ($(".seocu-quickinfo").parents("header.panel-heading").next("div").attr("aria-expanded") == "true") { seocuqi.html("<span>'.$result.'%</span>"); } else { seocuqi.html(\''.$quick.'\'); } $(".seocu-resultbar").css({ background: "'.$resultcol.'"}).animate({ width: "'.$result.'%" }); });</script>';
	
	
	//Erfolgreiche Tests ausgeben + Detailbutton
	$detaillink = (!$showchecks && $checks_ok > 0) ? '<li class="seocu-noicon"><div class="seocu-result" style="background: '.$resultcol.';">'.$checks_ok.' '.rex_i18n::msg('a1544_seo_tests_ok').'</div> <a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$actArt.'" data-seocu-cid="'.$actClang.'" data-seocu-aname="'.htmlspecialchars(a1544_removeTags($art->getName())).'">'.rex_i18n::msg('a1544_seo_details').'</a></li><li>&nbsp;</li>' : '';
	$cnt 		= str_replace("###detaillink###", $detaillink, $cnt);
	$analysis	= str_replace("###detaillink###", '', $analysis);


	//WDF-Button ausgeben
	$wdflink_name = rex_i18n::msg('a1544_seo_more');
	$wdflink_data = 'data-seocu-aid="'.$actArt.'" data-seocu-cid="'.$actClang.'" data-seocu-aname="'.htmlspecialchars(a1544_removeTags($art->getName())).'"';
	$wdflink_modal = ($showtests == 1) ? '' : 'data-toggle="modal" data-target="#seocu-modal"';
		$wdflink = '<a class="seoculist-morewdf seoculist-morewdf-sidebar" '.$wdflink_data.' '.$wdflink_modal.'>'.$wdflink_name.'</a>';
		$wdflink_detail = '<a class="seoculist-morewdf seoculist-morewdf-sidebar" '.$wdflink_data.'>'.$wdflink_name.'</a>';
		
	$cnt 		= str_replace("###wdflink###", $wdflink, $cnt);
	$analysis	= str_replace("###wdflink###", $wdflink_detail, $analysis);
	

	//Metadaten
	$mem_end = memory_get_usage();
	$time_end = microtime();
	

	//Result & Daten in DB speichern
	$db = rex_sql::factory();
		unset($data);
		$data['article_id'] = $actArt;
		$data['article_name'] = $art->getName();
		$data['cat_id'] = $actCat;
		$data['clang_id'] = $actClang;
		$data['keyword'] = $keyword;
		$data['result'] = $result;
		$data['analysis'] = $analysis;											//HTML-Code (Details)
		$data['flesch'] = @$flesch_score;
		$data['wdf'] = @$wdf;
		$data['seo_title'] = $title_raw;
		$data['seo_desc'] = $desc;
		$data['h1'] = array_map('a1544_removeSST', $h1);							//array
		$data['h2'] = array_map('a1544_removeSST', $h2);							//array
		$data['word_count'] = $wcount;
		$data['link_count_int'] = @$lcount_int;
		$data['link_count_ext'] = @$lcount_ext;
		
		$data['tests_success'] = $checks_ok;
		$data['tests_failed'] = $checks - $checks_ok;		
		$data['mem_start'] = $mem_start;
		$data['mem_end'] = $mem_end;
		$data['time_start'] = $time_start;
		$data['time_end'] = $time_end;
		
	$db->setQuery("UPDATE ".rex::getTable('article')." SET seocu_result = '".aFM_maskSql($result)."', seocu_data = '".aFM_maskSql(serialize($data))."', seocu_updatedate = '".time()."'  WHERE id = '".$actArt."' AND clang_id = '".$actClang."'");
	unset($db);
	
    
    //SERP-Snippet aufbereiten
	/*
	$ptitle = $title_raw;
	$ptitle = (mb_strlen(utf8_decode($ptitle)) > $config['be_seo_title_max']) ? mb_substr($ptitle, 0, ($config['be_seo_title_max']-3)).' ...' : $ptitle;
	$purl = $url;
		$purl = preg_replace("#^http[s]?://#i", "", $purl);
		$purlsep = " › ";
		$purl = str_replace("/", $purlsep, $purl);
			$purl = trim(preg_replace("#".$purlsep."$#i", "", $purl));
		
    $cnt .= '<div class="seocu-preview">';
		$cnt .= '<span class="seocu-head">'.rex_i18n::msg('a1544_seo_preview').'</span>';
		$cnt .= '<span class="seocu-preview-url">'.$purl.'</span>';
		$cnt .= '<span class="seocu-preview-title">'.aFM_maskChar($ptitle).'</span>';
		$cnt .= '<span class="seocu-preview-desc">'.aFM_maskChar(aFM_revChar($desc)).'</span>';
    $cnt .= '</div>';
	*/

	//Ausgabe in Sidebar & in Details
	$cnt .= ($showchecks || $config['be_seo_sidebar_snippet']) ? a1544_seocuSnippet($title_raw, $desc, $url) : '';
	
	

	/* SPÄÄÄÄTER vielleicht !!!
	//Navbar & Navcontent aufbereiten
	$cnt .= '<div class="seocu-navbar btn-group btn-group-xs">';
		$cnt .= '<a class="btn btn-default active">'.rex_i18n::msg('a1544_seo_preview').'</a>';
		$cnt .= '<a class="btn btn-default">Überschriften</a>';
		$cnt .= '<a class="btn btn-default">Bilder</a>';
		$cnt .= '<a class="btn btn-default">Links</a>';
	$cnt .= '</div>	';
	*/


	//leere Zeilen bereinigen
	$cnt = str_replace("<li>&nbsp;</li><li>&nbsp;</li>", "<li>&nbsp;</li>", $cnt);
	

	//hau wech den mist
	unset($config, $yr, $url, $html, $art, $artcnt_raw, $artcnt, $db);
	unset($title, $title_raw, $title_words, $desc, $desc_raw, $h1, $h1cnt, $imgs, $imgcnt, $links, $linknames, $tmp);
	unset($sents, $sylls, $flesch_score, $flesch_result, $asl, $asw, $result, $resultcol, $quick, $ptitle, $artdom, $path, $artdommp);
	
	
	//reset php-settings
	set_time_limit($time_limit);
    ini_set('memory_limit', $memory_limit);
		
	
	//gib mir alles zurück
	return ($mode == 'json') ? json_encode($data) : $cnt;
}


//entferne Scripte
function a1544_removeScript($str)
{	$regex_script = "/<script((?!src=)[^>])*?>.*?<\/script>/is";	//regex: Script-Bereiche --> kein U-Modifier, da bereits non-greedy
	
	return preg_replace($regex_script, "", $str);
}


//entferne Style
function a1544_removeStyle($str)
{	$regex_style = "/<style[^>]*?>.*?<\/style>/is";					//regex: Style-Bereiche --> kein U-Modifier, da bereits non-greedy

	return preg_replace($regex_style, "", $str);
}

//entferne Tags
function a1544_removeTags($str)
{	//Textformatierung entfernen, ohne Ersetzung (a, b, strong, i, em, u, font, s, sub, sup, strike, tt, del, ins, small, big, mark, span)
	$str = preg_replace("/<(a\b|b\b|strong\b|i\b|em\b|u\b|font\b|s\b|sup\b|sub\b|strike\b|tt\b|del\b|ins\b|small\b|big\b|mark\b|span\b)[^>]*>/is", '', $str);
	
		//Endzeichen entfernen
		$str = preg_replace("/<\/(a|b|strong|i|em|u|font|s|sup|sub|strike|tt|del|ins|small|big|mark|span)>/is", '', $str);

	//alle anderen Tags, Umbrüche, Tabstops mit Leerzeichen ersetzen
	$str = preg_replace("/<[^>]*>/is", ' ', $str);
	$str = str_replace("\r", '', $str);
	$str = str_replace("\n", ' ', $str);
	$str = str_replace("\t", ' ', $str);
	
	//restliche Tags entfernen und mehrfache Leerzeichen entfernen
	$str = strip_tags($str);	
	$str = trim(preg_replace('/ {2,}/', ' ', $str));
	
	/*
	echo "NACHHER:<br>";
	dump($str);
	exit();
	*/
	
	return $str;
}


//entferne Script, Style, Tags
function a1544_removeSST($str)
{	return a1544_removeTags(a1544_removeStyle(a1544_removeScript($str)));
}


//entferne HTML-Entities
function a1544_removeEntities($str)
{	$uml_s = array("&auml;", "&Auml;", "&ouml;", "&Ouml;", "&uuml;", "&Uuml;", "&szlig;", "&euro;");
	$uml_r = array("ä", "Ä", "ö", "Ö", "ü", "Ü", "ß", "€");
	$str = str_replace($uml_s, $uml_r, $str);
	
	$ent = array("&nbsp;", "&amp;", "&lt;", "&gt;", "&middot;", "&laquo;", "&raquo;", "&apos;", "&copy;", "&reg;", "&quot;", "&bull;");
	$str = str_replace($ent, " ", $str);

	return $str;
}


//auf Vokal prüfen
function a1544_hasVocal($str)
{	return (preg_match("/[aeiouyäöü]/i", $str)) ? true : false;
}


//Wörter zählen
function a1544_countWords($str, $op = "")
{	global $a1544_seps;
	
	$wc = 0; $words = array();
	if (!empty($str) && !empty($a1544_seps)):
		//Wörter zählen
		$str .= " #A1544_END_A1544#";
	
		for ($w = strtok($str, $a1544_seps); $w != "#A1544_END_A1544#"; $w = strtok($a1544_seps)):
			$w = trim(mb_strtolower($w));
			
			if ($w != "" && !is_numeric($w) && preg_match("/\p{L}+/i", $w)):		//mind. 1 Buchstabe muss im Wort vorkommen -> Prüfung auch in WDF
				$wc++;
				if ($op == 'array') { $words[] = $w; }
			endif;
		endfor;
	endif;
	
	return ($op == 'array') ? $words : $wc;
}


//rexAPI Klassen-Erweiterung (Ajax-Abfrage)
class rex_api_a1544_getSeocheckup extends rex_api_function
{	protected $published = false;		//true = auch im Frontend

	function execute()
	{	//SEO-Check abrufen
		$op = a1544_seocheckup();
		
		//Ajax-Rückgabe
		//rex_response::sendContent($op, 'text/html');
		rex_response::sendContent($op);
        exit();
	}
	
	/*
    public function requiresCsrfProtection()
    {	return true;
    }
	*/	
}

class rex_api_a1544_getSeocheckupWDF extends rex_api_function
{	protected $published = false;		//true = auch im Frontend

	function execute()
	{	//Variablen
		$actArt = rex_request('article_id', 'int');
		$actClang = rex_request('clang', 'int');
	
		$op = "";
	
		//WDF einladen und ausgeben		
		$sql = "SELECT seocu_data FROM ".rex::getTable('article')." WHERE id = '".$actArt."' AND clang_id = '".$actClang."' limit 0,1";
		$db = rex_sql::factory();
		$db->setQuery($sql);

		if ($db->getRows() > 0):
			$seo_data = @unserialize($db->getValue('seocu_data'));
				$seo_data = (!is_array($seo_data)) ? array() : $seo_data;
						
			//Daten erstellen
			$wdf = @$seo_data['wdf'];
			
			$wdflist = "";
			$wdfchart_counts = $wdfchart_names = array();
			if (is_array($wdf) && count($wdf) > 0):
				$wdflist .= '<div class="seocu-wdftable">';
			
				$wdflist .= '<table border="0" cellpadding="0" cellspacing="0"><tr>';
					$wdflist .= '<th>'.rex_i18n::msg('a1544_seo_wdf_table_keyword').'</th>';
					$wdflist .= '<th>'.rex_i18n::msg('a1544_seo_wdf_table_count').'</th>';
					$wdflist .= '<th title="'.rex_i18n::msg('a1544_seo_wdf_table_wdf_info').'">'.rex_i18n::msg('a1544_seo_wdf_table_wdf').'</th>';
					$wdflist .= '<th title="'.rex_i18n::msg('a1544_seo_wdf_table_density_info').'">'.rex_i18n::msg('a1544_seo_wdf_table_density').'</th>';
					$wdflist .= '</tr>';
			
				foreach ($wdf as $key=>$val):
					$wdflist .= '<tr>';
						$wdflist .= '<td>'.$key.'</td>';
						$wdflist .= '<td>'.$val['count'].'</td>';
						$wdflist .= '<td>'.$val['wdf'].'</td>';
						$wdflist .= '<td>'.$val['kd'].'</td>';
					$wdflist .= '</tr>';
					
					//Chartdaten setzen
					//$wdfchart_counts[] .= $val['count'];
					$wdfchart_counts[] .= $val['wdf'];
					$wdfchart_names[] = "'".str_replace("'", "&apos;", $key)."'";
				endforeach;
				
				$wdflist .= '</table>';
				$wdflist .= '</div>';
			endif;
			
			//Chart erstellen
			//$wdfchart = '<div id="seocu-wdfchart"></div>';
			$wdfchart = '<canvas id="seocu-wdfchart" width="768" height="250"></canvas>';
			$wdfchart .= '<script>';
				$wdfchart .= 'var wdf_counts = ['.implode(",", $wdfchart_counts).'];';
				$wdfchart .= 'var wdf_names = ['.implode(",", $wdfchart_names).'];';
			$wdfchart .= '$(document).ready(function(){ createSeocuchart(); });</script>';
			
			//Ausgabe
			$op .= '<span class="seocu-head seocu-first-head">'.rex_i18n::msg('a1544_seo_wdf_table_wdf').'</span>';
			$op .= $wdfchart;
			$op .= '<br><br>';
			$op .= $wdflist;
				
		else:
			//kein Datensatz gefunden
			$op = rex_i18n::msg('a1544_seo_wdf_notfound');
		endif;
	
		//Ajax-Rückgabe
		//rex_response::sendContent($op, 'text/html');
		rex_response::sendContent($op);
        exit();
	}
}



//zusätzliche JS-Skript-Vars in BE einbinden
function a1544_seocuJS($ep)
{	//Variablen deklarieren
	global $a1544_mypage;
	$cnt = "";
	
	//Vorgaben einlesen
	$op = $ep->getSubject();										//Content des ExtPoint (z.B. Seiteninhalt)

	//CSS und JS anfügen
	$l1 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_title'));
	$l2 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_analyze'));
	$l3 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_close'));
	$l4 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_error'));
	$l5 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_legibility'));
	$l6 = aFM_maskChar(rex_i18n::msg('a1544_seo_modal_artnotfound'));
	$l7 = aFM_maskChar(rex_i18n::msg('a1544_seo_details'));
	$l8 = aFM_maskChar(rex_i18n::msg('a1544_seo_title_nok'));
	$l9 = aFM_maskChar(rex_i18n::msg('a1544_seo_desc_nok'));
	$l10 = aFM_maskChar(rex_i18n::msg('a1544_seo_h1_nok'));
	$l11 = aFM_maskChar(rex_i18n::msg('a1544_seo_more'));
	
	$search[0] 	= '</head>';
	$replace[0] = '<script type="text/javascript">var seoculang_modal = {"addonname":"'.$a1544_mypage.'","title":"'.$l1.'","analyze":"'.$l2.'","close":"'.$l3.'","error":"'.$l4.'","legibility":"'.$l5.'","artnotfound":"'.$l6.'","detail":"'.$l7.'","title_nok":"'.$l8.'","desc_nok":"'.$l9.'","h1_nok":"'.$l10.'","more":"'.$l11.'"};</script></head>';
	
	$op = str_replace($search, $replace, $op);
	return $op;
}


//Snippet generieren
function a1544_seocuSnippet($title = "", $desc = "", $url = "", $class = "")
{	global $a1544_mypage;

	//Addon-Konfig einladen
	$config = rex_addon::get($a1544_mypage)->getConfig('config');
		$config['be_seo_title_max'] 		= (!isset($config['be_seo_title_max']))			? '65' : $config['be_seo_title_max'];

	//SEO-Vars aufbereiten	
	$title = (mb_strlen(utf8_decode($title)) > $config['be_seo_title_max']) ? mb_substr($title, 0, ($config['be_seo_title_max']-3)).' ...' : $title;
	$title = aFM_maskChar($title);
	$desc = aFM_maskChar(aFM_revChar($desc));
	
	$urlsep = " › ";
	$url = preg_replace("#^http[s]?://#i", "", $url);
	$url = str_replace("/", $urlsep, $url);
	$url = trim(preg_replace("#".$urlsep."$#i", "", $url));
	
	//Snippet erstellen
    $snip = '<div class="seocu-preview '.$class.'">';
		$snip .= '<span class="seocu-head">'.rex_i18n::msg('a1544_seo_preview').'</span>';
		$snip .= '<span class="seocu-preview-url">'.$url.'</span>';
		$snip .= '<span class="seocu-preview-title">'.$title.'</span>';
		$snip .= '<span class="seocu-preview-desc">'.$desc.'</span>';
    $snip .= '</div>';
	
	return $snip;
}


//Wortliste generieren (für WDF)
function a1544_seocuWDF($content = "")
{	global $a1544_mypage;
	global $a1544_seps;
	
	//Addon-Konfig einladen
	$config = rex_addon::get($a1544_mypage)->getConfig('config');
		$config['be_seo_wdf_countwords'] 		= (!isset($config['be_seo_wdf_countwords']))	? '20' : $config['be_seo_wdf_countwords'];
		$config['be_seo_wdf_skipshortwords']	= (@$config['be_seo_wdf_skipshortwords'] == 'checked') ? true : false;
	
	//Stopwörter
	//Stopwords-Basis-Set nach Update auf 1.4 einladen
	$stopwords = rex_file::get(rex_addon::get($a1544_mypage)->getPath('data/stopwords.txt'));
		$stopwords = trim($stopwords);

	$stopwords = (!isset($config['be_seo_wdf_stopwords'])) ? $stopwords : $config['be_seo_wdf_stopwords'];
		$stopwords = str_replace("\r\n", "\n", $stopwords);
	$stopwords = (!empty($stopwords)) ? explode("\n", $stopwords) : array();
			
	//WDF kalkulieren			
	$wdflist = array();
	if (!empty($content) && !empty($a1544_seps) && count($stopwords) > 0):
		//Wörter zählen
		$wc = $wc_wostops = 0;
		$wc_token = array();

		$words = a1544_countWords($content, 'array');
		foreach ($words as $word):
			if ($word != ""):
				if (!in_array($word, $stopwords) && !is_numeric($word) && preg_match("/\p{L}+/i", $word)):			//mind. 1 Buchstabe muss im Wort vorkommen -> Prüfung auch in countWords()
					if ($config['be_seo_wdf_skipshortwords'] && mb_strlen($word) < 4) { continue; }
					
					$wc_token[$word]++; 									//gefundene Wörter
					$wc_wostops++;											//Anzahl Wörter ohne Stopwörter > nur zur Information, falls irgendwann mal benötigt
				endif;

				$wc++;
			endif;
		endforeach;
		
		ksort($wc_token);													//nach Schlüssel sortieren (1-xx)
		arsort($wc_token);													//array rückwärts sortieren (xx-1)
		
		
		//WDF berechnen
		$count = intval($config['be_seo_wdf_countwords']);
			$count = ($count < 1) ? 5 : $count;								//5 Wörter sind Std.
		
		$i=0;	
		foreach ($wc_token as $key=>$val):
			//Keyword density
			$kd = round((($val*100) / $wc), 2);
			$kd = number_format($kd, 2, '.', '');
		
			//WDF
			$w1 = (log($val+1, 2));
			$w2 = (log($wc, 2));
			$wdf = round($w1/$w2, 10);
			$wdf = number_format($wdf, 5, '.', '');
			
			//Ergebnis speichern
			$wdflist[$key]['count'] = $val;
			$wdflist[$key]['kd'] = $kd;
			$wdflist[$key]['wdf'] = $wdf;
		
			$i++; 
			if ($i == $count) break; 
		endforeach;
	endif;	
	
	/*
	echo "$wc<br>";
	echo "$wc_wostops<br>";
	dump($wdflist);
	exit();
	*/
	
	return $wdflist;	
}

?>