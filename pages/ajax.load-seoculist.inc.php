<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: AJAX Loader - SEO-CheckUp-Liste
	v1.4
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//Variablen
$page = rex_request('page', 'string');
$subpage = "";																//ggf. manuell setzen
$subpage2 = rex_be_controller::getCurrentPagePart(3);						//2. Unterebene = dritter Teil des page-Parameters
	$subpage2 = preg_replace("/.*-([0-9])$/i", "$1", $subpage2);			//Auslesen der ClangID

$sbeg = trim(urldecode(rex_request('sbeg')));
$id = rex_request('id', 'int');
//$aid = rex_request('aid', 'int');
//$cat = rex_request('cat', 'int');
//$gid = (rex_request('gid') == 'checked') ? true : false;

$order = (strtolower(rex_request('order')) == 'desc') ? 'DESC' : 'ASC';

$limStart = rex_request('limStart', 'int');


//Sessionwerte zurücksetzen
$_SESSION['as_sbeg_seoculist'] = $_SESSION['as_id_seoculist'] = "";


//AJAX begin
echo '<!-- ###AJAX### -->';


//SQL erstellen und Filterung berücksichtigen
$sql = "SELECT pid, id, path, status, clang_id, name, seocu_keyword, seocu_result, seocu_data FROM ".rex::getTable('article');
$sql_where = " WHERE 1";


//Eingrenzung: Suchbegriff
if (!empty($sbeg)):
	$_SESSION['as_sbeg_seoculist'] = $sbeg;
	$sql_where .= " AND ( 
					BINARY LOWER(name) like LOWER('%".aFM_maskSql($sbeg)."%') 
					OR BINARY LOWER(yrewrite_title) like LOWER('%".aFM_maskSql($sbeg)."%')
					OR BINARY LOWER(id) like LOWER('".aFM_maskSql($sbeg)."')
					)";
					//BINARY sorgt für einen Binärvergleich, wodurch Umlaute auch als Umlaute gewertet werden (ohne BINARY ist ein Ä = A)
					//LOWER sorgt für einen Vergleich auf Basis von Kleinbuchstaben (ohne LOWER würde das BINARY nach Groß/Klein unterscheiden)
					//DATE_FORMAT wandelt den Wert in eine andere Schreibweise um (damit kann der gespeicherte Wert vom gesuchten Wert abweichen) --> DATE_FORMAT(`date`, '%e.%m.%Y')
					//FROM_UNIXTIME arbeit wie DATE-FORMAT, aber benötigt als Quelle einen timestamp
					//		OR ( FROM_UNIXTIME(`date`, '%e.%m.%Y') like '".aFM_maskSql($sbeg)."%' OR FROM_UNIXTIME(`date`, '%d.%m.%Y') like '".aFM_maskSql($sbeg)."%' )
endif;


//Eingrenzung: Sprache (clangID)
$sql_where .= ($subpage2 > 0) ? " AND clang_id = '".$subpage2."'" : '';


//Eingrenzung On-/Offline
$sql_where .= (!$config['be_seo_offlinearts']) ? " AND status = '1'" : '';


//Sortierung
//$sql_where .= " ORDER BY CONVERT(seocu_result, DECIMAL) ".$order.", name ASC, id ASC";
$sql_where .= " ORDER BY name ".$order.", CONVERT(seocu_result, DECIMAL) ASC, id ASC";


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
$addPath = "index.php?page=content/edit";
	

            if ($db->getRows() > 0):
                //Liste ausgeben
                for ($i=0; $i < $db->getRows(); $i++):
					$eid = intval($db->getValue('id'));
					$cid = intval($db->getValue('clang_id'));
					$editPath = $addPath.'&amp;article_id='.$eid.'&amp;clang='.$cid.'&amp;mode=edit';
						
					$curstat = intval($db->getValue('status'));
					$status = ($curstat != 1) ? 'class="rex-offline"' : '';
					$statusinfo = ($curstat != 1) ? ' title="'.$this->i18n('a1544_bas_list_article').' '.$this->i18n('a1544_offline').'"' : '';
					
					$name = $db->getValue('name');
					//$name = strip_tags($name, '<br>');
					$name = strip_tags($name);
					
					$prot = 'http://';
					$url = $prot.$_SERVER['SERVER_NAME'].rex_getUrl($eid, $cid);
						//mit Daten aus yRewrite abgleichen
						if (rex_addon::get('yrewrite')->isAvailable()):
							$prot = (rex_yrewrite::isHttps()) ? 'https://' : $prot;
							$url = rex_yrewrite::getFullUrlByArticleId($eid, $cid);
						endif;
					$d_url = (!empty($url)) ? '<br><span class="seoculist-url">'.$url.'</span>' : $url;
					
						
					//SEO-Daten
					$seo_keyword = $db->getValue('seocu_keyword');
					$seo_result = intval($db->getValue('seocu_result'));
					$seo_data = @unserialize($db->getValue('seocu_data'));
						$seo_data = (!is_array($seo_data)) ? array() : $seo_data;
					
					$d_flesch = @$seo_data['flesch'];
						$d_flesch = (preg_match("/[0-9,.]/", $d_flesch)) ? $d_flesch : 0;
					
					$d_title = @$seo_data['seo_title'];
					$d_desc = @$seo_data['seo_desc'];
						/*
						$d_seo = "";
						if ((!empty($d_title) && @$config['be_seo_culist_title']) || (!empty($d_desc) && @$config['be_seo_culist_desc'])):
							$d_seo .= '<table class="seoculist-metadata" cellpadding="0" cellspacing="0">';
								$d_seo .= (!empty($d_title) && @$config['be_seo_culist_title']) ? '<tr><td>'.$this->i18n('a1544_bas_list_title').':</td><td>'.$d_title.'</td></tr>' : '';
								$d_seo .= (!empty($d_desc) && @$config['be_seo_culist_desc']) ? '<tr><td class="seoculist-metadata-col1">'.$this->i18n('a1544_bas_list_desc').':</td><td>'.$d_desc.'</td></tr>' : '';
							$d_seo .= '</table>';
						endif;
						*/
					
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
								
								$d_wdflist .= (!empty($d_wdflist)) ? '<br /><a class="seoculist-morewdf" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$eid.'" data-seocu-cid="'.$cid.'" data-seocu-aname="'.htmlspecialchars($name).'">'.$this->i18n('a1544_seo_more').'</a>' : '';
							endif;
							
							$d_wdflist = (empty($seo_data)) ? '-' : $d_wdflist;
							$seo_cols .= '<td class="seoculist-wdf" data-title="'.$this->i18n('a1544_bas_list_wdf').'">'.$d_wdflist.'</td>';
						endif;
						
					
					//Kurz-Analyse
					$d_result = "";
					if (!empty($seo_data)):
						$resultcol = "#3BB594";
							$resultcol = ($seo_result > 70 && $seo_result < 90) ? "#CEB964" : $resultcol;
							$resultcol = ($seo_result >= 50 && $seo_result <= 70) ? "#F90" : $resultcol;
							$resultcol = ($seo_result > 30 && $seo_result < 50) ? "#EC7627" : $resultcol;
							$resultcol = ($seo_result <= 30) ? "#D9534F" : $resultcol;
						
						$d_result .= '<div class="seocu-result" style="background:'.$resultcol.'">'.$seo_result.'/100</div>';
							if (@$config['be_seo_checks_flesch'] == 'checked' || @$config['be_seo_checks_selection'] != 'checked'):
								$d_result .= '<div class="seocu-result seocu-result-info">'.$this->i18n('a1544_seo_modal_legibility').': '.$d_flesch.'</div>';
							endif;
						$d_result .= '<br /><a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'.$eid.'" data-seocu-cid="'.$cid.'" data-seocu-aname="'.htmlspecialchars($name).'">'.$this->i18n('a1544_seo_details').'</a>';
					else:
						$d_result .= '<span class="rex-offline">'.$this->i18n('a1544_seo_nottested').'</span>';
					endif;
					
					
					//Ausgabe
                    ?>
                        
                    <tr id="entry<?php echo $eid; ?>">
                        <td class="rex-table-id"><?php echo $eid; ?></td>
                        <td class="seoculist-name" data-title="<?php echo $this->i18n('a1544_bas_list_name'); ?>"><a href="<?php echo $editPath; ?>" target="_blank" <?php echo $status.$statusinfo; ?>><?php echo $name; ?></a><?php echo $d_url.$d_snippet; ?></td>
                        
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
									<input class="form-control" type="text" name="seocu-keyword" value="<?php echo aFM_maskChar($seo_keyword); ?>" placeholder="<?php echo $this->i18n('a1544_seo_keyword'); ?>" data-seocu-aid="<?php echo $eid; ?>" data-seocu-cid="<?php echo $cid; ?>" />
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
					echo '<tr><td colspan="8" align="center">';
					
					for ($i=0; $i<$maxSite; $i++):
						$sel = ($i == $limStart) ? 'ajaxNavSel' : '';
						echo '<span class="ajaxNav '.$sel.'" data-navsite="'.$i.'">'.($i+1).'</span>';
					endfor;
					
					echo '</td></tr>';
				endif;
				
            else:
                ?>
                
                    <tr>
                        <td colspan="8" align="center"> - <?php echo $this->i18n('a1544_search_notfound'); ?> -</td>
                    </tr>
                
                <?php
            endif;

//AJAX end
echo '<!-- ###/AJAX### -->';
?>