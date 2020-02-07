<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: AJAX Loader - SEO-CheckUp-Liste
	v1.3.4
	by Falko Müller @ 2019-2020
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
$sql = "SELECT pid, id, path, status, clang_id, name, status, seocu_keyword, seocu_result, seocu_data FROM ".rex::getTable('article');
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
$sql_where .= ($config['be_seo_offlinearts'] != 'checked') ? " AND status = '1'" : '';


//Sortierung
//$sql_where .= " ORDER BY CONVERT(seocu_result, DECIMAL) ".$order.", name ASC, id ASC";
$sql_where .= " ORDER BY name ".$order.", CONVERT(seocu_result, DECIMAL) ASC, id ASC";


//Limit
$limStart = ($limStart > 0) ? $limStart : 0;
$limCount = 25;
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
										
					$curstat = $db->getValue('status');
					$status = '<span class="rex-offline"><i class="rex-icon rex-icon-offline"></i> '.$this->i18n('a1544_offline').'</span>';
						$status = ($curstat == 1) ? '<span class="rex-online"><i class="rex-icon rex-icon-online"></i> '.$this->i18n('a1544_online').'</span>' : $status;

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
					$url = (!empty($url)) ? '<br><span class="seoculist-url">'.$url.'</span>' : $url;
						
					//SEO-Daten
					$seo_keyword = $db->getValue('seocu_keyword');
					$seo_result = intval($db->getValue('seocu_result'));
					$seo_data = @unserialize($db->getValue('seocu_data'));
						$seo_data = (!is_array($seo_data)) ? array() : $seo_data;
					
					$d_flesch = @$seo_data['flesch'];
					$d_tests_ok = intval(@$seo_data['tests_success']);
					$d_tests_nok = intval(@$seo_data['tests_failed']);
                    ?>
                        
                    <tr id="entry<?php echo $eid; ?>">
                        <td class="rex-table-id"><?php echo $eid; ?></td>
                        <td class="seoculist-name"><a href="<?php echo $editPath; ?>" target="_blank"><?php echo $name; ?></a><?php echo $url; ?></td>
                        <td class="seoculist-nowrap"><?php echo $status; ?></td>                        
                        <td class="seoculist-data">
                        	<?php
                            if (!empty($seo_data)):
								$resultcol = "#3BB594";
									$resultcol = ($seo_result > 70 && $seo_result < 90) ? "#CEB964" : $resultcol;
									$resultcol = ($seo_result >= 50 && $seo_result <= 70) ? "#F90" : $resultcol;
									$resultcol = ($seo_result > 30 && $seo_result < 50) ? "#EC7627" : $resultcol;
									$resultcol = ($seo_result <= 30) ? "#D9534F" : $resultcol;
								?>
								<div class="seocu-result" style="background: <?php echo $resultcol; ?>;"><?php echo $seo_result; ?>/100</div>
								<div class="seocu-result seocu-result-info"><?php echo $this->i18n('a1544_seo_modal_legibility'); ?>: <?php echo $d_flesch; ?></div>
								<br />
								<a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="<?php echo $eid; ?>" data-seocu-cid="<?php echo $cid; ?>" data-seocu-aname="<?php echo htmlspecialchars($name); ?>"><?php echo $this->i18n('a1544_seo_details'); ?></a>
                            <?php
							else:
								echo $this->i18n('a1544_seo_nottested');
							endif;
							?>
                        </td>
                        <td>
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