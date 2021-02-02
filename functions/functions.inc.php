<?php
/*
	Redaxo-Addon SEO-CheckUp
	Globale-Funktionen
	v1.4.1
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//aktive Session prüfen


//globale Variablen
//global $a1544_mypage;
//$a1544_mypage = $this->getProperty('package');


//Funktionen
//Ajax-Inhalte holen
if (!function_exists('aFM_bindAjax')):
	function aFM_bindAjax($ep)
	{	$op = $ep->getSubject();
		$op = preg_replace('/(.*<\!-- ###AJAX### -->)(.*)(<\!-- ###\/AJAX### -->.*)/s', '$2', $op);
		return $op;
	}
endif;


//Inhalte aufbereiten
if (!function_exists('aFM_getMonthName')):
	function aFM_getMonthName($int)
	{	//wandelt numerische Monatszahl in geschriebene Monatsnamen
		//Aufruf: $var = aFM_getMonthName(5);
		$m[1] = 'Januar';
		$m[2] = 'Februar';
		$m[3] = 'März';
		$m[4] = 'April';
		$m[5] = 'Mai';
		$m[6] = 'Juni';
		$m[7] = 'Juli';
		$m[8] = 'August';
		$m[9] = 'September';
		$m[10] = 'Oktober';
		$m[11] = 'November';
		$m[12] = 'Dezember';
	
		$int = intval($int);
		return ($int > 0) ? $m[$int] : $int;
	}
endif;
if (!function_exists('aFM_fillNull')):
	function aFM_fillNull($str = "", $stellen = 2)
	{	//füllt den Wert mit führenden nullen auf
		//Aufruf: $var = aFM_fillNull($str);		$var = aFM_fillNull($string, integer);
		return (!empty($str) || $str == 0) ? str_pad($str, $stellen, 0, STR_PAD_LEFT) : $str;
	}
endif;
if (!function_exists('aFM_arrayString')):
	function aFM_arrayString($str = "", $out = 'array', $del = '#')
	{	//bereitet Array-String als Ausgabe oder Array auf
		//Aufruf: $var = aFM_arrayString($str);		$var = aFM_arrayString($string|$array, '#', 'string|array');
		$op = array();
		
		if (!empty($str)):
			$out = ($out == 'string') ? 'string' : 'array';
			$del = (empty($del)) ? '#' : $del;
			
			$tmp = (is_array($str)) ? $str : explode($del, $str);
				foreach ($tmp as $val):
					if (!empty($val)):
						array_push($op, $val);
						continue;
					endif;
				endforeach;
		endif;
		
		return ($out == 'array') ? $op : implode(", ", $op);
	}
endif;


//Maskierungen + Tags
if (!function_exists('aFM_maskChar')):
	function aFM_maskChar($str)
	{	//Maskiert folgende Sonderzeichen: & " < > '
		$str = stripslashes($str);
		$str = htmlspecialchars($str, ENT_QUOTES);
		$str = trim($str);
		
		return $str;
	}
endif;
if (!function_exists('aFM_maskArray')):
	function aFM_maskArray($arr)
	{	if (is_array($arr)):
			$arr = array_map(function($str) { return aFM_maskChar($str); }, $arr);
		endif;
		
		return $arr;
	}
endif;
if (!function_exists('aFM_maskSingleQuote')):
	function aFM_maskSingleQuote($str)
	{	//Ersetzt Single-Quotes: '
		return str_replace("'", "&#039;", $str);
	}
endif;
if (!function_exists('aFM_maskDoubleQuote')):
	function aFM_maskDoubleQuote($str)
	{	//Ersetzt Double-Quotes: "
		return str_replace('"', "&quot;", $str);
	}
endif;
if (!function_exists('aFM_maskSql')):
	function aFM_maskSql($str)
	{	//Maskiert desn Wert für DB-Abfrage
		$s = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    	$r = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
		return str_replace($s, $r, $str);
	}
endif;

if (!function_exists('aFM_unmaskQuotes')):
	function aFM_unmaskQuotes($str)
	{	//Demaskiert folgende Anführungszeichen: " '
		return str_replace(array("&quot;", "&#039;"), array('"', "'"), $str);
	}
endif;
if (!function_exists('aFM_revChar')):
	function aFM_revChar($str)
	{	//Demaskiert folgende Sonderzeichen: & " < > '
		$chars = array("&amp;amp;quot;"=>'"', "&amp;quot;"=>'"', "&amp;"=>"&", "&lt;"=>"<", "&gt;"=>">", "&quot;"=>'"', "&#039;"=>"'");
		foreach ($chars as $key => $value):
			$str = str_replace($key, $value, $str);
		endforeach;
		
		return $str;
	}
endif;

if (!function_exists('aFM_blockTags')):
	function aFM_blockTags($str)
	{	//Entfernt bekannte Tags (PHP, JS, HTML)
		if ($str != ""):
			$str = stripslashes($str);
			$str = str_replace("\xc2\xa0", ' ', $str);	//&nbsp; als UTF8 ersetzen in nortmales WhiteSpace
			$str = strip_tags($str);
				$phps = array("/<\?php/i", "/<\?/i", "/<%/i", "/<script language=\"php\">/i", "/<script language='php'>/i", "/\?>/i", "/%>/i");
					foreach ($phps as $key):
						$str = preg_replace($key, "", $str);
					endforeach;
				$js = array("/<script.*>/i", "/<\/script>/i");
					foreach ($js as $key):
						$str = preg_replace($key, "", $str);
					endforeach;
			$str = trim($str);
		endif;
		
		return $str;
	}
endif;
if (!function_exists('aFM_noQuote')):
	function aFM_noQuote($str)
	{	//Ersetzt Double-Quotes: "
		return str_replace('"', "'", $str);
	}
endif;
if (!function_exists('aFM_textOnly')):
	function aFM_textOnly($str, $nobreak = false)
	{	//Entfernt HTML-Tags, Zeilenumbrüche und Tabstops
		if ($str != ""):
			$str = stripslashes($str);
			$str = str_replace("\xc2\xa0", ' ', $str);	//&nbsp; als UTF8 ersetzen in nortmales WhiteSpace
			$str = str_replace("\t", ' ', $str);		//Tabstop (\t) ersetzen in normales WhiteSpace
			$str = strip_tags(nl2br($str));
			$str = ($nobreak) ? str_replace(array("\r\n","\n\r", "\n", "\r"), "", $str) : $str;
			$str = trim($str);
		endif;
		
		return $str;
	}
endif;


//Strip-Tags Funktion, zur besserern Entfernung von Tags ab PHP 5.3.4
if (!function_exists('aFM_stripTags')):
	function aFM_stripTags($str, $allowed_tags = array())
	{	//Aufrufe: aFM_stripTags($str);			aFM_stripTags($str, array('h1','a'));
		$allowed_tags = array_map(strtolower, $allowed_tags);
	
		$rstr = preg_replace_callback('/<\/?([^>\s]+)[^>]*>/i', function ($matches) use (&$allowed_tags) {
			return in_array(strtolower($matches[1]), $allowed_tags) ? $matches[0] : '';
		}, $str);
		
		return $rstr;
	}
endif;


//Wandelt AJAX-Parameter wieder korrekt in UTF8 um
if (!function_exists('aFM_utf8urldecode')):
	function aFM_utf8urldecode($str) {
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;", urldecode($str));
		return html_entity_decode($str, null, 'UTF-8');
	} 
endif;


//Texte kürzen inkl. Beachtung von HTML-Tags
if (!function_exists('aFM_substr')):
	function aFM_substr($str, $limit=100, $isUtf8=true)
	{	$ret = ""; $retLength = $position = 0;
		$tags = array();
		
		$re = ($isUtf8) ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}' : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';
		
		while ($retLength < $limit && preg_match($re, $str, $match, PREG_OFFSET_CAPTURE, $position)):
			list($tag, $tagPosition) = $match[0];
		
			//print text leading up to the tag
			$tmp = substr($str, $position, $tagPosition - $position);
			
			if ($retLength + strlen($tmp) > $limit):
				$ret .= substr($tmp, 0, $limit - $retLength);
				$retLength = $limit;
				break;
			endif;
			
			$ret .= $tmp;
			$retLength += strlen($tmp);
			
			if ($retLength >= $limit) break;
			
			if ($tag[0] == '&' || ord($tag) >= 0x80):
				//pass the entity or UTF-8 multibyte sequence through unchanged
				$ret .= $tag;
				$retLength++;
			else:
				//handle the tag
				$tagName = $match[1][0];
				
				if ($tag[1] == '/'):
					//this is a closing tag
					$openingTag = array_pop($tags);
					assert($openingTag == $tagName); 			//check that tags are properly nested		
					$ret .= $tag;
				elseif ($tag[strlen($tag) - 2] == '/'):
					//self-closing tag
					$ret .= $tag;
				else:
					//opening tag
					$ret .= $tag;
					$tags[] = $tagName;
				endif;
			endif;	
			
			$position = $tagPosition + strlen($tag);			//continue after the tag
		endwhile;
		
		if ($retLength < $limit && $position < strlen($str)) { $ret .= substr($str, $position, $limit - $retLength); }		//print any remaining text
		while (!empty($tags)) $ret .= '</'.array_pop($tags).'>';																	//close any open tags
		
		return $ret;
	}
endif;


//Funktionen: Addon-only