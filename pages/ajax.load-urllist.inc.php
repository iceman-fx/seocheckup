<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: AJAX Loader - SEO-CheckUp URL-Addon-Liste
	v1.6.6
	by Falko Müller @ 2019-2023
	package: redaxo5
*/

//Variablen
$page = rex_request('page', 'string');
$subpage = "";																						//ggf. manuell setzen
$subpage2 = rex_be_controller::getCurrentPagePart(3);												//2. Unterebene = dritter Teil des page-Parameters
	$subpage2 = (!empty($subpage2)) ? preg_replace("/.*-([0-9])$/i", "$1", $subpage2) : '';

$sbeg = trim(urldecode(rex_request('sbeg')));
$id = rex_request('id', 'int');

$order = (strtolower(rex_request('order')) == 'desc') ? 'DESC' : 'ASC';

$limStart = rex_request('limStart', 'int');


//Sessionwerte zurücksetzen
$_SESSION['as_sbeg_urllist'] = $_SESSION['as_id_urllist'] = "";


//AJAX begin
echo '<!-- ###AJAX### -->';


//SQL erstellen und Filterung berücksichtigen
$sql = "SELECT id, profile_id, url, seocu_keyword, seocu_result, seocu_data FROM ".rex::getTable('url_generator_url');
$sql_where = " WHERE 1";


//Eingrenzung: Suchbegriff
if (!empty($sbeg)):
	$_SESSION['as_sbeg_urllist'] = $sbeg;
	$sql_where .= " AND ( 
					BINARY LOWER(url) like LOWER('%".aFM_maskSql($sbeg)."%') 
					)";
					//BINARY sorgt für einen Binärvergleich, wodurch Umlaute auch als Umlaute gewertet werden (ohne BINARY ist ein Ä = A)
					//LOWER sorgt für einen Vergleich auf Basis von Kleinbuchstaben (ohne LOWER würde das BINARY nach Groß/Klein unterscheiden)
					//DATE_FORMAT wandelt den Wert in eine andere Schreibweise um (damit kann der gespeicherte Wert vom gesuchten Wert abweichen) --> DATE_FORMAT(`date`, '%e.%m.%Y')
					//FROM_UNIXTIME arbeit wie DATE-FORMAT, aber benötigt als Quelle einen timestamp
					//		OR ( FROM_UNIXTIME(`date`, '%e.%m.%Y') like '".aFM_maskSql($sbeg)."%' OR FROM_UNIXTIME(`date`, '%d.%m.%Y') like '".aFM_maskSql($sbeg)."%' )
endif;


//Eingrenzung: URL-Addon-Profile
$sql_where .= ($subpage2 > 0) ? " AND profile_id = '".$subpage2."'" : '';


//Sortierung
$sql_where .= " ORDER BY url ".$order.", id ASC";


//Limit
$limStart = ($limStart > 0) ? $limStart : 0;
$limCount = (intval(@$config['be_seo_culist_count']) > 0) ? intval(@$config['be_seo_culist_count']) : 20;
$sql_limit = " LIMIT ".($limStart * $limCount).",".$limCount;


//SQL zwischenspeichern
//$_SESSION['as_sql_aktuelles'] = $sql.$sql_where;


//Ergebnisse nachladen
	//echo "<tr><td colspan='7'>$sql$sql_where$sql_limit</td></tr>";
$db = rex_sql::factory();
$db->setQuery($sql.$sql_where.$sql_limit);
	

            if ($db->getRows() > 0):
                //Liste ausgeben
                for ($i=0; $i < $db->getRows(); $i++):
					$eid = intval($db->getValue('id'));
					$pid = intval($db->getValue('profile_id'));
					
					$prot = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')) ? 'https://' : 'http://'; 
					$prot = (rex_addon::get('yrewrite')->isAvailable() && rex_yrewrite::isHttps()) ? 'https://' : $prot;

					$url = preg_replace("#^/{1,2}#", "", $db->getValue('url'));
					$url = (preg_match("#^//#", $db->getValue('url'))) ? $prot.$url : $prot.str_replace("//", "/", $_SERVER['SERVER_NAME'].'/'.$url);
					//$d_url = (!empty($url)) ? '<br><span class="seoculist-url">'.$url.'</span>' : $url;
					
						
					//SEO-Daten
					$seo_keyword = $db->getValue('seocu_keyword');
					$seo_result = intval($db->getValue('seocu_result'));
					$seo_data = @unserialize($db->getValue('seocu_data'));
						$seo_data = (!is_array($seo_data)) ? array() : $seo_data;
					
					$d_flesch = @$seo_data['flesch'];
						$d_flesch = (!empty($d_flesch) && preg_match("/[0-9,.]/", $d_flesch)) ? $d_flesch : 0;
					
					$d_title = aFM_maskChar(@$seo_data['seo_title']);
					$d_desc = aFM_maskChar(@$seo_data['seo_desc']);
					
					//$d_snippet = (@$config['be_seo_culist_snippet']) ? a1544_seocuSnippet($d_title, $d_desc, $url, "seoculist-preview") : '';
					$d_snippet = "";		//deaktiviert, da es zu viel PLatz wegnimmt und man es nicht zwingend in der Liste benötigt
					
					$d_wdf = @$seo_data['wdf'];
						
					$d_tests_ok = intval(@$seo_data['tests_success']);
					$d_tests_nok = intval(@$seo_data['tests_failed']);
					
					
					//Spalten & deren Werte aufbereiten
					$cols = 0; $seo_cols = "";
						if ($config['be_seo_culist_title']):
							$cols++;
							
							$d_title = (empty($d_title)) ? '<span class="rex-offline">'.$this->i18n('a1544_seo_title_nok').'</span>' : $d_title;
							$d_title = (empty($seo_data)) ? '-' : $d_title;
							$seo_cols .= '<td class="seoculist-title" data-title="'.$this->i18n('a1544_bas_list_title').'"><div class="seocu-scroll">'.$d_title.'</div></td>';
						endif;
						if ($config['be_seo_culist_desc']):
							$cols++;
							
							$d_desc = (empty($d_desc)) ? '<span class="rex-offline">'.$this->i18n('a1544_seo_desc_nok').'</span>' : $d_desc;
							$d_desc = (empty($seo_data)) ? '-' : $d_desc;
							$seo_cols .= '<td class="seoculist-desc" data-title="'.$this->i18n('a1544_bas_list_desc').'"><div class="seocu-scroll">'.$d_desc.'</div></td>';
						endif;
						if ($config['be_seo_culist_h1']):
							$cols++;
							
							$d_h1 = (is_array(@$seo_data['h1'])) ? implode("<br><br>", @$seo_data['h1']) : '';	
							$d_h1 = (empty($d_h1)) ? '<span class="rex-offline">'.$this->i18n('a1544_seo_h1_nok').'</span>' : $d_h1;
							$d_h1 = (empty($seo_data)) ? '-' : $d_h1;
							$seo_cols .= '<td class="seoculist-h1" data-title="'.$this->i18n('a1544_bas_list_h1').'"><div class="seocu-scroll">'.$d_h1.'</div></td>';
						endif;
						if ($config['be_seo_culist_h2']):
							$cols++;
							
							$d_h2 = (is_array(@$seo_data['h2'])) ? implode("<br><br>", @$seo_data['h2']) : '';
							$d_h2 = (empty($d_h2)) ? '-' : $d_h2;							
							$seo_cols .= '<td class="seoculist-h2" data-title="'.$this->i18n('a1544_bas_list_h2').'"><div class="seocu-scroll">'.$d_h2.'</div></td>';
						endif;
						if ($config['be_seo_culist_links'] && $config['be_seo_checks_links']):
							$cols++;
							
							$d_links = (isset($seo_data['link_count_int']) || isset($seo_data['link_count_ext'])) ? @$seo_data['link_count_int'].'/'.@$seo_data['link_count_ext'] : '-';							
							$seo_cols .= '<td class="seoculist-links" data-title="'.$this->i18n('a1544_bas_list_links').'">'.$d_links.'</td>';
						endif;
						if ($config['be_seo_culist_words']):
							$cols++;
							
							$d_words = (isset($seo_data['word_count'])) ? intval($seo_data['word_count']) : '-';							
							$seo_cols .= '<td class="seoculist-words" data-title="'.$this->i18n('a1544_bas_list_words').'">'.$d_words.'</td>';
						endif;						
						if ($config['be_seo_culist_wdf'] && $config['be_seo_checks_wdf']):
							$cols++;
							
							$d_wdflist = "";
							if (is_array($d_wdf) && count($d_wdf) > 0):
								$w=0;
								foreach ($d_wdf as $key=>$val):
									$d_wdflist .= $key.'&nbsp;('.$val['count'].')<br>';
									$w++;
									if ($w == 5) { break; }
								endforeach;
								
								$d_wdflist .= (!empty($d_wdflist)) ? '<br /><a class="seoculist-morewdf" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$eid.'" data-seocu-url="'.aFM_noQuote($url).'" data-seocu-aname="'.aFM_maskChar($url).'">'.$this->i18n('a1544_seo_more').'</a>' : '';
							endif;
							
							$d_wdflist = (empty($seo_data)) ? '-' : $d_wdflist;
							$seo_cols .= '<td class="seoculist-wdf" data-title="'.$this->i18n('a1544_bas_list_wdf').'">'.$d_wdflist.'</td>';
						endif;
						
					
					//Kurz-Analyse
					$d_result = ""; $cssFlesch = 'seoculist-hideFlesch';
					if (!empty($seo_data)):
						$resultcol = "col1";
							$resultcol = ($seo_result > 70 && $seo_result < 90) 	? "col2" : $resultcol;
							$resultcol = ($seo_result >= 50 && $seo_result <= 70) 	? "col3" : $resultcol;
							$resultcol = ($seo_result > 30 && $seo_result < 50) 	? "col4" : $resultcol;
							$resultcol = ($seo_result <= 30) 						? "col5" : $resultcol;
						
						$d_result .= '<div class="seocu-result seocu-result-'.$resultcol.'bg">'.$seo_result.'/100</div>';
							if (@$config['be_seo_checks_flesch'] == 'checked' || @$config['be_seo_checks_selection'] != 'checked'):
								$d_result .= '<div class="seocu-result seocu-result-info">'.$this->i18n('a1544_seo_modal_legibility').': '.$d_flesch.'</div>';
								$cssFlesch = '';
							endif;
						$d_result .= '<br /><a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$eid.'" data-seocu-url="'.aFM_noQuote($url).'" data-seocu-aname="'.aFM_maskChar($url).'">'.$this->i18n('a1544_seo_details').'</a>';
					else:
						$d_result .= '<span class="rex-offline">'.$this->i18n('a1544_seo_nottested').'</span>';
					endif;
					
					
					//Ausgabe
                    ?>
                        
                    <tr id="entry<?php echo $eid; ?>" class="<?php echo $cssFlesch; ?>">
                        <td class="rex-table-id"><?php echo $eid; ?></td>
                        <td class="seoculist-name" data-title="<?php echo $this->i18n('a1544_bas_list_name'); ?>"><a href="<?php echo $url; ?>" target="_blank"><?php echo aFM_textOnly($url); ?></a><?php echo $d_snippet; ?></td>
                        
                        <?php
						echo $seo_cols;
						
                        if ($cols <= 3):
							echo '<td class="seoculist-data">'.$d_result.'</td>';
						endif;
                        ?>
                        
                        <td class="seoculist-keyword">
                        	<form class="seoculist-form">
                        	<div class="rex-js-widget">
								<div class="input-group">
									<input class="form-control" type="text" name="seocu-keyword" value="<?php echo aFM_maskChar($seo_keyword); ?>" placeholder="<?php echo $this->i18n('a1544_seo_keyword'); ?>" data-seocu-aid="<?php echo $eid; ?>" data-seocu-url="<?php echo aFM_noQuote($url); ?>" />
									<span class="input-group-btn">
										<a class="btn btn-popup" title="<?php echo $this->i18n('a1544_seo_refresh'); ?>"><i class="rex-icon fa-refresh"></i></a>
									</span>
								</div>
							</div>
                            </form>
                            
                            <?php if ($cols > 3) { echo '<div class="seoculist-analysis seoculist-data">'.$d_result.'</div>'; } ?>
						</td>
                    </tr>

                    <?php
					$db->next();
                endfor;
				
				
				//Seitenschaltung generieren
				$dbl = rex_sql::factory();
				$dbl->setQuery($sql.$sql_where);
					$maxEntry = $dbl->getRows();
					$maxSite = ceil($maxEntry / $limCount);

				if ($dbl->getRows() > $limCount):
					echo '<tr><td colspan="10" align="center"><ul class="addon_list-pagination pagination">';
					
					for ($i=0; $i<$maxSite; $i++):
						$sel = ($i == $limStart) ? 'ajaxNavSel' : '';
						$selLI = ($i == $limStart) ? 'active' : '';
						echo '<li class="rex-page '.$selLI.'"><span class="ajaxNav '.$sel.'" data-navsite="'.$i.'">'.($i+1).'</span></li>';
					endfor;
					
					echo '</ul></td></tr>';
				endif;
				
            else:
                ?>
                
                    <tr>
                        <td colspan="10" align="center"> - <?php echo $this->i18n('a1544_search_notfound'); ?> -</td>
                    </tr>
                
                <?php
            endif;

//AJAX end
echo '<!-- ###/AJAX### -->';
?>