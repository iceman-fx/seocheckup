<?php
/*
	Redaxo-Addon SEO-CheckUp
	Backend-Funktionen (SEO)
	v1.3.2
	by Falko Müller @ 2019
	package: redaxo5
*/

//aktive Session prüfen


//globale Variablen


//Funktionen
//SEO-CheckUp einbinden
function a1544_showSEO($ep)
{	global $a1544_mypage;
	
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
        
        <div class="modal fade bd-example-modal-lg" id="seocu-modal" tabindex="-1" role="dialog">
        	<div class="modal-dialog modal-dialog-centered" role"document">
            	<div class="modal-content">
                	<div class="modal-header"><div class="modal-title">$l3</div></div>
                    <div class="modal-body seocheckup">$l4</div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">$l5</button></div>
                </div>
            </div>
        </div>
	</div>
	<script type="text/javascript">$(function(){ var seocubtn = $(".seocheckup a");	$(".seocheckup form").on('submit', function(e){ e.preventDefault(); }); seocubtn.click(function(){ seocubtn.addClass("rotate"); 	
	urldata = "rex-api-call=a1544_getSeocheckup&keyword="+encodeURIComponent($(".seocheckup input").val())+"&lasturl="+encodeURIComponent(window.location.href);
	$("#seocheckup").load("", urldata, function(){ seocubtn.removeClass("rotate"); }); }); seocubtn.trigger('click'); $(document).on("rex:ready", function(){ seocubtn.trigger('click'); }); });</script>
EOD;

	//SEO-Panel erstellen und ausgeben
	$collapsed = (@$config['be_seo_opened'] != 'checked') ? true : false;
	$frag = new rex_fragment();
		$frag->setVar('title', '<div class="seocu-title"><i class="rex-icon fa-stethoscope"></i> '.rex_i18n::msg('a1544_seo_head').'<div class="seocu-resultbar"></div><div class="seocu-quickinfo"></div></div>', false);
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
	$cnt = "";
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
	
	$keyword = strtolower(urldecode(rex_request('keyword')));
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
		$config['be_seo_removeblock_header']= (!isset($config['be_seo_removeblock_header']))? 'checked' : $config['be_seo_removeblock_header'];
		$config['be_seo_removeblock_footer']= (!isset($config['be_seo_removeblock_footer']))? 'checked' : $config['be_seo_removeblock_footer'];
		$config['be_seo_removeblock_nav'] 	= (!isset($config['be_seo_removeblock_nav'])) 	? 'checked' : $config['be_seo_removeblock_nav'];
		
		$config['be_seo_offlinekeywords'] 	= (!isset($config['be_seo_offlinekeywords'])) 	? '' : $config['be_seo_offlinekeywords'];
		$config['be_seo_title_min'] 		= (!isset($config['be_seo_title_min']))			? '50' : $config['be_seo_title_min'];
		$config['be_seo_title_max'] 		= (!isset($config['be_seo_title_max']))			? '65' : $config['be_seo_title_max'];
		$config['be_seo_title_words'] 		= (!isset($config['be_seo_title_words'])) 		? '6' : $config['be_seo_title_words'];
		$config['be_seo_desc_min'] 			= (!isset($config['be_seo_desc_min'])) 			? '130' : $config['be_seo_desc_min'];
		$config['be_seo_desc_max'] 			= (!isset($config['be_seo_desc_max'])) 			? '160' : $config['be_seo_desc_max'];
		$config['be_seo_desc_words'] 		= (!isset($config['be_seo_desc_words'])) 		? '12' : $config['be_seo_desc_words'];
		$config['be_seo_content_words'] 	= (!isset($config['be_seo_content_words'])) 	? '300' : $config['be_seo_content_words'];
		$config['be_seo_density_min'] 		= (!isset($config['be_seo_density_min'])) 		? '300' : $config['be_seo_density_min'];
		$config['be_seo_density_max'] 		= (!isset($config['be_seo_density_max'])) 		? '4' : $config['be_seo_density_max'];
		$config['be_seo_url_max'] 			= (!isset($config['be_seo_url_max'])) 			? '55' : $config['be_seo_url_max'];
		$config['be_seo_url_depths'] 		= (!isset($config['be_seo_url_depths'])) 		? '3' : $config['be_seo_url_depths'];
		$config['be_seo_links'] 			= (!isset($config['be_seo_links'])) 			? '3' : $config['be_seo_links'];
	
	$showchecks = ((isset($config['be_seo_showchecks']) && $config['be_seo_showchecks'] == 'checked') || $showtests) ? true : false;
		
	$regex_script = "/<script((?!src=)[^>])*?>.*?<\/script>/is";	//regex: Script-Bereiche --> kein U-Modifier, da bereits non-greedy
	$regex_style = "/<style[^>]*?>.*?<\/style>/is";					//regex: Style-Bereiche --> kein U-Modifier, da bereits non-greedy
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
			endif;
		} catch(rex_socket_exception $e) { $sockerror = $e->getMessage(); }
		
		$httpheader = (is_array($httpheader)) ? implode("|", $httpheader) : str_replace(array("\r\n", "\n"), "|", $httpheader);
		$hasRedirect = (preg_match("/http[\/0-9\.]* 30[0-9]{1,1}/i", $httpheader) && stripos($httpheader, "|location:") !== false) ? true : false;
		unset($httpheader);
		
		//Redaxo-Artikel bzw. Content holen
		if (!$hasRedirect):
			/*	
			$art = new rex_article_content($actArt, $actClang);													
			$artcnt_raw = trim($art->getArticle());											//macht Probleme, da bloecks-Slices trotz Frontedmode ausgegeben werden
			unset($art);
			*/
			//Fallback aus FE-Content	-->		//nicht mehr nutzen, da sonst die Berechnung der Wortanzahl falsch ist (header, footer, nav Bereiche zählen sonst mit) !!!
			$artcnt_raw = (empty($artcnt_raw)) ? preg_replace("/^[\s\S]*<body[^\>]*>([\s\S]*)<\/body>[\s\S]*$/im", "$1", $html) : $artcnt_raw;				
		endif;
		rex::setProperty('redaxo', true);
		
		
		//Content aufbereiten
		$html = trim($html);
		$artcnt_raw = trim($artcnt_raw);
			$artcnt_raw = ($config['be_seo_removeblock_header'] == 'checked') 	? preg_replace("/<header[^\>]*>.*<\/header>/imsU", "", $artcnt_raw) : $artcnt_raw;			//--> U-Modifier, da sonst greedy
			$artcnt_raw = ($config['be_seo_removeblock_footer'] == 'checked') 	? preg_replace("/<footer[^\>]*>.*<\/footer>/imsU", "", $artcnt_raw) : $artcnt_raw;			//--> U-Modifier, da sonst greedy
			$artcnt_raw = ($config['be_seo_removeblock_nav'] == 'checked') 		? preg_replace("/<nav[^\>]*>.*<\/nav>/imsU", "", $artcnt_raw) : $artcnt_raw;				//--> U-Modifier, da sonst greedy
		$arthead = trim(substr($html, 0,stripos($html, "</head>")));
		unset($html);
		
        $artcnt = $artcnt_raw;
            $artcnt = preg_replace($regex_script, "", $artcnt);																				//Script-Blöcke entfernen
            $artcnt = preg_replace($regex_style, "", $artcnt);																				//Style-Blöcke entfernen
			$artcnt = trim(preg_replace($regex_wspace, " ", strip_tags($artcnt)));															//Tags entfernen, mehrfache Leerzeichen vereinfachen
		$artcnt_wo_h1 = preg_replace("/<h1[^>]*>.*?<\/h1>/is", "", $artcnt_raw);															//Content ohne H1-Überschriften --> kein U-Modifier, da bereits non-greedy
            $artcnt_wo_h1 = preg_replace($regex_script, "", $artcnt_wo_h1);																	//Script-Blöcke entfernen
            $artcnt_wo_h1 = preg_replace($regex_style, "", $artcnt_wo_h1);																	//Style-Blöcke entfernen
			$artcnt_wo_h1 = trim(preg_replace($regex_wspace, " ", strip_tags($artcnt_wo_h1)));												//Tags entfernen, mehrfache Leerzeichen vereinfachen		
		
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
/*
$cnt .= <<<EOD
        <div class="modal fade bd-example-modal-lg" id="seocu-modal" tabindex="-1" role="dialog">
        	<div class="modal-dialog modal-dialog-centered" role"document">
            	<div class="modal-content">
                	<div class="modal-header"><div class="modal-title">SEO-CheckUp</div></div>
                    <div class="modal-body seocheckup">Analyse wird geladen ...</div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button></div>
                </div>
            </div>
        </div>
EOD;
*/		
    	$cnt .= '<span class="seocu-head seocu-first-head">'.rex_i18n::msg('a1544_seo_tests').'</span>';																	//<span class="seocu-result"></span>
        $cnt .= '<ul>';
		$cnt .= '###detaillink###';
		
        
		//Einzelwerte aufbereiten
		preg_match("/<title[^>]*>(.*?)<\/title>/is", $arthead, $matches);																									//Title holen --> kein U-Modifier, da bereits non-greedy
			$title = (!isset($matches[1]) || empty($matches[1])) ? $yrs->getTitle() : $matches[1];
			$title = trim(preg_replace("/\s\s+/", " ", $title));
			$title_raw = aFM_unmaskQuotes($title);
			$title_words = (!empty($title_raw)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", $title_raw))) ) : array();			//Wörter in title finden
			
		preg_match("/<meta name\s*=\s*[\"']{1}description[\"']{1}[ ]+content\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>/is", $arthead, $matches);								//Description holen
			$desc = (!isset($matches[1]) || empty($matches[1])) ? $yrs->getDescription() : $matches[1];
			$desc = trim(preg_replace("/\s\s+/", " ", $desc));
			$desc_raw = aFM_unmaskQuotes($desc);
			
		preg_match_all("/<h1[^>]*>(.*?)<\/h1[^>]*>/is", $artcnt_raw, $matches);																								//H1-Überschrift(en) holen --> kein U-Modifier, da bereits non-greedy
			$h1 = $h1cnt = (isset($matches[1])) ? $matches[1] : '';
			$h1cnt = (is_array($h1cnt)) ? implode(" ", $h1cnt) : $h1cnt;
				$h1cnt = preg_replace($regex_script, "", $h1cnt);																											//Script-Blöcke entfernen
				$h1cnt = preg_replace($regex_style, "", $h1cnt);																											//Style-Blöcke entfernen
				$h1cnt = trim(preg_replace($regex_wspace, " ", strip_tags($h1cnt)));																						//Tags entfernen
			$h1_words = (!empty($h1cnt)) ? explode(" ", trim(preg_replace($regex_wspace, " ", preg_replace($regex_pmarks, " ", aFM_unmaskQuotes($h1cnt)))) ) : array();		//Wörter in H1 finden

		preg_match_all("/<h([0-6]{1})[^>]*>(.*?)<\/h[0-6]{1}[^>]*>/is", $artcnt_raw, $matches);																				//alle Überschriften holen --> kein U-Modifier, da bereits non-greedy
			$hx = (isset($matches[2])) ? $matches[2] : '';
			$hx_types = (isset($matches[1])) ? $matches[1] : '';
			
		$has_og = (float)preg_match_all("/<meta property\s*=\s*[\"']{1}og:[^\"']*[\"']{1}[ ]+content\s*=\s*[\"']{1}[^\"']*[\"']{1}[^>]*>/iU", $arthead);					//Anzahl OG-Tags holen --> U-Modifier, da sonst greedy
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
		
		preg_match_all("/<img([\w\W]+?)\/>/is", $artcnt_raw, $matches);																										//alle Bilder holen --> kein U-Modifier, da bereits non-greedy
			$imgs = (isset($matches[1])) ? $matches[1] : array();																											//alt: /<img [^\/>]*\/>/isU
			$imgcnt = (is_array($imgs)) ? implode(" ", $imgs) : $imgs;
		
		preg_match_all("/<a [^>]*href\s*=\s*[\"']{1}([^\"']*)[\"']{1}[^>]*>(.*)<\/a>/isU", $artcnt_raw, $matches);															//alle Verlinkungen holen (a href) --> U-Modifier, da sonst greedy
			$links = (isset($matches[1])) ? $matches[1] : array();
			//$linknames = (isset($matches[2])) ? $matches[2] : array();

		
		//allgemeine Prüfungen (title, desc, opengraph (title, desc, url), h1, content)
		//title
		if (!empty($title)):
			$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_title_ok').'</li>' : '';
			$checks_ok++;
			
			if (strlen($title_raw) < $config['be_seo_title_min']):
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_title_min'], $config['be_seo_title_max']), rex_i18n::rawmsg('a1544_seo_title_short')).'</li>';
			elseif (strlen($title_raw) > $config['be_seo_title_max']):
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_title_min'], $config['be_seo_title_max']), rex_i18n::rawmsg('a1544_seo_title_long')).'</li>';
			else:
				$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_title_opt').'</li>' : '';
				$checks_ok++;
			endif;
			$checks++;
			
			$cnt .= ($showchecks && count($title_words) >= $config['be_seo_title_words']) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_title_words').'</li>' : '';
		else:
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_title_nok').'</li>';
		endif;
		$checks++;
		

		//desc
		if (!empty($desc)):
			$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_desc_ok').'</li>' : '';
			$checks_ok++;
			
			if (strlen($desc_raw) < $config['be_seo_desc_min']):
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_desc_min'], $config['be_seo_desc_max']), rex_i18n::rawmsg('a1544_seo_desc_short')).'</li>';
			elseif (strlen($desc_raw) > $config['be_seo_desc_max']):
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###min###", "###max###"), array($config['be_seo_desc_min'], $config['be_seo_desc_max']), rex_i18n::rawmsg('a1544_seo_desc_long')).'</li>';
			else:
				$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_desc_opt').'</li>' : '';
				$checks_ok++;
			endif;
			$checks++;
			
			$cnt .= ($showchecks && count(explode(" ", $desc)) >= $config['be_seo_desc_words']) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_desc_words').'</li>' : '';
		else:
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_desc_nok').'</li>';
		endif;
		$checks++;
		
		
		//opengraph
        if ($has_og):
            $cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_og_ok').'</li>' : '';
            $checks_ok++;
            
            if (!empty($ogtitle)):
                $cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogtitle_ok').'</li>' : '';
                $checks_ok++;
            else:
                $cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogtitle_nok').'</li>';
            endif;
            $checks++;

            if (!empty($ogdesc)):
                $cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogdesc_ok').'</li>' : '';
                $checks_ok++;
            else:
                $cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogdesc_nok').'</li>';
            endif;
            $checks++;

            if (!empty($ogurl)):
                $cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_ogurl_ok').'</li>' : '';
                $checks_ok++;
            else:
                $cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_ogurl_nok').'</li>';
            endif;
            $checks++;
        endif;
		$checks++;
		
		
		//URL Länge
		$tmp = preg_replace("/(http[s]?:\/\/|\/$)/i", "", str_replace($dom, "", $url));
			$tmp = preg_replace("/^\//i", "", $tmp);
		if (strlen($tmp) > $config['be_seo_url_max']):
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###max###"), array($config['be_seo_url_max']), rex_i18n::rawmsg('a1544_seo_url_length_nok')).'</li>';
		else:
			$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_url_length_ok').'</li>' : '';
			$checks_ok++;
		endif;
		$checks++;
		
		
		//URL Verzeichnistiefe
		$tmp = explode("/", $tmp);
		if (count($tmp) > $config['be_seo_url_depths']):
			$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###max###"), array($config['be_seo_url_depths']), rex_i18n::rawmsg('a1544_seo_url_depth_nok')).'</li>';
		else:
			$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_url_depth_ok').'</li>' : '';
			$checks_ok++;
		endif;
		$checks++;		
		
		
		//H1
		if (count($h1) > 1):
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_h1multi').'</li>';
		elseif (empty($h1[0])):
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_h1_nok').'</li>';
		else:
			$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_h1_ok').'</li>' : '';
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
				$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_hx_ok').'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i><span class="seocu-infolistswitch" data-seocu-dst="#seocu-hxlist">'.rex_i18n::msg('a1544_seo_hx_nok').'&nbsp;<span class="rex-icon fa-caret-down"></span></span>'.$hxlist.'</li>';
			endif;
			$checks++;
		endif;
		unset($hxlist, $hxerror);
		
		
		//content
		$wcount = (!empty($artcnt)) ? count(explode(" ", $artcnt)) : 0;
		if ($wcount >= $config['be_seo_content_words']):
			$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###words###", $wcount, rex_i18n::msg('a1544_seo_cnt_ok')).'</li>' : '';
			$checks_ok++;
		else:
			$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###words###", "###cwords###"), array($wcount, $config['be_seo_content_words']), rex_i18n::msg('a1544_seo_cnt_short')).'</li>';
		endif;
		$checks++;
		
		
		//content -> gefundene title Wörter
		$found = 0;
		foreach ($title_words as $word):
			//if (strstr(strtolower($artcnt_wo_h1), strtolower($word))) { $found++; }
			if (stristr($artcnt_wo_h1, $word)) { $found++; }
		endforeach;
		
		if (count($title_words) == $found):
			$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_titlewords_ok').'</li>' : '';
			$checks_ok++;
		else:
			$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_titlewords_nok').'</li>';
		endif;
		$checks++;
		
		
		//content -> gefundene H1 Wöter
		$found = 0;
		foreach ($h1_words as $word):
			//if (strstr(strtolower($artcnt_wo_h1), strtolower($word))) { $found++; }
			if (stristr($artcnt_wo_h1, $word)) { $found++; }
		endforeach;
		
		if (count($h1_words) == $found):
			$cnt .= ($showchecks) ? '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_h1words_ok').'</li>' : '';
			$checks_ok++;
		else:
			$cnt .= '<li class="'.$css_sub.'"><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_cnt_h1words_nok').'</li>';
		endif;
		$checks++;
		
		
		//images
		$acount = 0;
		if (count($imgs) > 0):
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
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i><span class="seocu-infolistswitch" data-seocu-dst="#seocu-imglist">'.str_replace("###count###", $acount, rex_i18n::msg('a1544_seo_img_nok')).'&nbsp;<span class="rex-icon fa-caret-down"></span></span>'.$imglist.'</li>';
			else:
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_img_ok').'</li>' : '';
				$checks_ok++;
			endif;
			$checks++;
		endif;
		unset($imglist);
		
		
		//links
		$lcount = $lcount_int = $lcount_ext = $lcount_intimg = 0;
		if (count($links) > 0):
			foreach ($links as $link):
				$link = trim($link);
				
				if (!empty($link)):
					if (preg_match("/^#/i", $link)): continue; endif;

					$lcount++;
					if (stristr($link, $dom) || !preg_match("/^(http[s]?:\/\/)/i", $link)):
						if (preg_match("/\.(jpg|jpeg|gif|png|svg)$/i", $link)):
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
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.str_replace(array("###intlinks###", "###extlinks###"), array($lcount_int, $lcount_ext), rex_i18n::msg('a1544_seo_links_nok')).'</li>';
			else:
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.str_replace(array("###intlinks###", "###extlinks###"), array($lcount_int, $lcount_ext), rex_i18n::msg('a1544_seo_links_ok')).'</li>' : '';
				$checks_ok++;
			endif;
			$checks++;
		endif;
			
	
		//Keyword-Prüfungen (title, desc, url, opengraph, h1, density, unique, content)
		if (!empty($keyword)):
			$cnt .= ($showchecks || $checks > $checks_ok) ? '<li>&nbsp;</li>' : '';
		
			//title
			//if (strstr(strtolower($title_raw), $keyword)):
			if (stristr($title_raw, $keyword)):
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keytitle_ok').'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keytitle_nok').'</li>';
			endif;
			$checks++;
			
			
			//desc
			//if (strstr(strtolower($desc_raw), $keyword)):
			if (stristr($desc_raw, $keyword)):
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keydesc_ok').'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keydesc_nok').'</li>';
			endif;
			$checks++;
			
			
			//url
			//if (strstr(strtolower($url), $keyword)):
			if (!stristr($url, $keyword)):
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_nok').'</li>';
			elseif (substr_count($url, $keyword) > 1):
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_multi').'</li>';
			else:
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyurl_ok').'</li>' : '';
				$checks_ok++;
			endif;
			$checks++;
			
			
			//h1
			//if (strstr(strtolower($h1cnt), $keyword)):
			if (stristr($h1cnt, $keyword)):
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyh1_ok').'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyh1_nok').'</li>';
			endif;
			$checks++;
			
			
			//content
			//preg_match_all("/".$keyword."/iU", $artcnt, $matches);
			//$kcountbody = count(@$matches[0]);
			$kcountbody = (float)preg_match_all("/".$keyword."/iU", $artcnt);				//U-Modifier, da sonst greedy
			if ($kcountbody > 0):
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###count###", $kcountbody, rex_i18n::msg('a1544_seo_keycnt_ok')).'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keycnt_nok').'</li>';
			endif;
			$checks++;
			
			
			//images
			if (count($imgs) > 0):
				//preg_match_all("/".$keyword."/iU", $imgcnt, $matches);
				//$kcountimg = count(@$matches[0]);
				$kcountimg = (float)preg_match_all("/".$keyword."/iU", $imgcnt);				//U-Modifier, da sonst greedy
				if ($kcountimg > 0):
					$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_ok').'</li>' : '';
					$checks_ok++;
				else:
					$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.rex_i18n::msg('a1544_seo_keyimg_nok').'</li>';
				endif;
				$checks++;
			endif;
			
					
			//density
			$tmp = ($wcount > 0) ? round( (float)($kcountbody / $wcount) * 100, 1) : 0;
				$l = str_replace(array("###min###", "###max###", "###density###"), array($config['be_seo_density_min'], $config['be_seo_density_max'], $tmp), rex_i18n::rawmsg('a1544_seo_density_nok'));
				
			if ($tmp >= $config['be_seo_density_min'] && $tmp <= $config['be_seo_density_max']):
				$cnt .= ($showchecks) ? '<li><i class="rex-icon '.$icon_ok.'"></i>'.str_replace("###density###", $tmp, rex_i18n::msg('a1544_seo_density_ok')).'</li>' : '';
				$checks_ok++;
			else:
				$cnt .= '<li><i class="rex-icon '.$icon_nok.'"></i>'.$l.'</li>';
			endif;
			$checks++;
			
				
			$cnt .= '<li>&nbsp;</li>';
			
			
			//Info: unique or multi keyword (Abfrage aus DB)
			$artdom = rex_yrewrite::getDomainByArticleId($actArt);
			$artdommp = intval($artdom->getMountId());
				$path = ($artdommp > 0) ? "%|$artdommp|%" : "|%";

			$db = rex_sql::factory();
				$offkeys = ($config['be_seo_offlinekeywords'] == 'checked') ? "" : " AND status = '1'";
			$db->setQuery("SELECT id FROM ".rex::getTable('article')." WHERE seocu_keyword = '".aFM_maskSql($keyword)."' AND path LIKE '".$path."'".$offkeys);
				$tmp = ($db->getRows() <= 1) ? rex_i18n::msg('a1544_seo_keyunique') : rex_i18n::msg('a1544_seo_keymulti');
			$cnt .= '<li><i class="rex-icon '.$icon_info.'"></i>'.$tmp.'</li>';
			unset($db);
		endif;
		
		
		//Info: no images
		$cnt .= (count($imgs) <= 0) ? '<li><i class="rex-icon '.$icon_info.'"></i>'.rex_i18n::msg('a1544_seo_img_notfound').'</li>' : '';
		
		
		$cnt .= (empty($keyword)) ? '<li>&nbsp;</li>' : '';
		
		
		//Flesch-Index
		$tmp = $artcnt;
			$tmp = preg_replace("/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{2,4})/i", "$1-$2-$3", $tmp);						//deutsches Datum ersetzen, um Sätze bessere zählen zu können
		$sents = (float)preg_match_all("/([^\.\!\?]+[\.\?\!]*)/", $tmp);												//Sätze anhand der üblichen Satzzeichen trennen
		$sylls = 0;																										//Silben anhand der Vokale zählen
			$tmp = strlen($artcnt);
			for ($i=0; $i < $tmp; $i++):
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
		
		
        $cnt .= '</ul>';
    $cnt .= '</div>';
	
	
	//Resultat aufbereiten
	$result = ($checks > 0) ? round( (float)($checks_ok * 100 / $checks), 0) : 0;
	$resultcol = "#3BB594";
		$resultcol = ($result > 70 && $result < 90) ? "#CEB964" : $resultcol;
		$resultcol = ($result >= 50 && $result <= 70) ? "#F90" : $resultcol;
		$resultcol = ($result > 30 && $result < 50) ? "#EC7627" : $resultcol;
		$resultcol = ($result <= 30) ? "#D9534F" : $resultcol;
	$quick = ($artChanged) ? '<span class="info"><i class="rex-icon fa-exclamation-triangle"></i></span>' : '<span>'.$result.'%</span>';
	$cnt .= '<script type="text/javascript">$(function(){ seocuqi = $(".seocu-quickinfo"); seocuqi.css({ color: "'.$resultcol.'"}); if ($(".seocu-quickinfo").parents("header.panel-heading").next("div").attr("aria-expanded") == "true") { seocuqi.html("<span>'.$result.'%</span>"); } else { seocuqi.html(\''.$quick.'\'); } $(".seocu-resultbar").css({ background: "'.$resultcol.'"}).animate({ width: "'.$result.'%" }); });</script>';
	
	
	//Erfolgreiche Tests ausgeben
	$detaillink = (!$showchecks && $checks_ok > 0) ? '<li class="seocu-noicon"><div class="seocu-result" style="background: '.$resultcol.';">'.$checks_ok.' '.rex_i18n::msg('a1544_seo_tests_ok').'</div> <a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$actArt.'" data-seocu-cid="'.$actClang.'" data-seocu-aname="'.htmlspecialchars(strip_tags($art->getName())).'">'.rex_i18n::msg('a1544_seo_details').'</a></li><li>&nbsp;</li>' : '';
	$cnt = str_replace("###detaillink###", $detaillink, $cnt);


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
		$data['flesch'] = $flesch_score;
		$data['tests_success'] = $checks_ok;
		$data['tests_failed'] = $checks - $checks_ok;
		
		$data['mem_start'] = $mem_start;
		$data['mem_end'] = $mem_end;
		$data['time_start'] = $time_start;
		$data['time_end'] = $time_end;
	$db->setQuery("UPDATE ".rex::getTable('article')." SET seocu_result = '".aFM_maskSql($result)."', seocu_data = '".serialize($data)."'  WHERE id = '".$actArt."' AND clang_id = '".$actClang."'");
	unset($db);
	
    
    //Vorschau aufbereiten
	$ptitle = (strlen($title_raw) > $config['be_seo_title_max']) ? substr($title_raw, 0, ($config['be_seo_title_max']-3)).' ...' : $title_raw;
	$ptitle = trim($ptitle);
		/* Variante mit Einkürzung nach Wörtern
		$ptitle = "";
			$tmp = explode(" ", $title_raw);
			foreach($tmp as $t):
				$t = trim($t);
				$ptitle .= (strlen($ptitle." ".$t) <= $config['be_seo_title_max']) ? " ".$t : "";
			endforeach;
		$ptitle = trim($ptitle);
		$ptitle .= ($ptitle < $title) ? ' ...' : '';
		*/
		
    $cnt .= '<div class="seocu-preview">';
		$cnt .= '<span class="seocu-head">'.rex_i18n::msg('a1544_seo_preview').'</span>';
		$cnt .= '<span class="seocu-preview-title">'.aFM_maskChar(aFM_revChar($ptitle)).'</span>';
		$cnt .= '<span class="seocu-preview-url">'.$url.'</span>';
		$cnt .= '<span class="seocu-preview-desc">'.aFM_maskChar(aFM_revChar($desc)).'</span>';
    $cnt .= '</div>';


	/* SPÄÄÄÄTER vielleicht !!!
	//Navbar & Navcontent aufbereiten
	$cnt .= '<div class="seocu-navbar btn-group btn-group-xs">';
		$cnt .= '<a class="btn btn-default active">'.rex_i18n::msg('a1544_seo_preview').'</a>';
		$cnt .= '<a class="btn btn-default">Überschriften</a>';
		$cnt .= '<a class="btn btn-default">Bilder</a>';
		$cnt .= '<a class="btn btn-default">Links</a>';
	$cnt .= '</div>	';
	*/


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

//auf Vokal prüfen
function a1544_hasVocal($str)
{	return (preg_match("/[aeiouyäöü]/i", $str)) ? true : false;
}


//rexAPI Klassen-Erweiterung (Ajax-Abfrage)
class rex_api_a1544_getSeocheckup extends rex_api_function
{	protected $published = false;		//true = auch im Frontend

	function execute()
	{	//Ajax-URL-Parameter einlesen
		//$var = rex_request('var', 'int');
		
		//Kategorien + Artikel auslesen
		$op = a1544_seocheckup();
		
		//Ajax-Rückgabe
		header('Content-type: text/html; charset=UTF-8');
		exit($op);		//Rückgabe ausgeben + Anfrage beenden
	}
}


//zusätzliche JS-Skripte in BE einbinden
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
	
	$search[0] 	= '</head>';
	$replace[0] = '<script type="text/javascript">var seoculang_modal = {"addonname":"'.$a1544_mypage.'","title":"'.$l1.'","analyze":"'.$l2.'","close":"'.$l3.'","error":"'.$l4.'","legibility":"'.$l5.'","artnotfound":"'.$l6.'","detail":"'.$l7.'"};</script></head>';
	
	$op = str_replace($search, $replace, $op);
	return $op;
}
?>