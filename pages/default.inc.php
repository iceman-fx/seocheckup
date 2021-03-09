<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Hauptseite (Default)
	v1.4.5
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//Variablen deklarieren
$mode = rex_request('mode');
$id = intval(rex_request('id'));
$form_error = 0;

$_SESSION['as_sbeg_seoculist'] = (!isset($_SESSION['as_sbeg_seoculist'])) ? "" : $_SESSION['as_sbeg_seoculist'];


//Datumsformate
$format_date = 'd.m.Y';
$format_online = 'd.m.Y H:i';


//Übersichtsliste laden + ausgeben
// --> wird per AJAX nachgeladen !!!

$addpath = "index.php?page=".$page;
?>


<section class="rex-page-section">
	<div class="panel panel-default">
	
		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1544_overview').' '.$this->i18n('a1544_default'); ?></div></header>  
		  
		<script type="text/javascript">
		jQuery(function() {
			//Ausblenden - Elemente
			jQuery('.search_options').hide();
			
			//Formfeld fokussieren
			jQuery('#s_sbeg').focus();
		
			//Liste - Filtern
			var params = 'page=<?php echo $page; ?>&subpage=load-seoculist&sbeg=';
			var dst = '#ajax_jlist';
			
			jQuery('#db-order').click(function() {
				var btn = jQuery(this);
				btn.toggleClass('db-order-desc');
					if (btn.hasClass('db-order-desc')) { btn.attr('data-order', 'desc'); } else { btn.attr('data-order', 'asc'); }
				loadAJAX(params + getSearchParams(), dst, 0);
			});
			
			jQuery('#s_sbeg').keyup(function(){												loadAJAX(params + getSearchParams(), dst, 0);	});
			jQuery('#s_button').click(function(){											loadAJAX(params + getSearchParams(), dst, 0);	});
			jQuery('#s_resetsbeg').click(function(){		jQuery('#s_sbeg').val("");
															loadAJAX(params, dst, 0);	});
															
			jQuery(document).on('click', 'span.ajaxNav', function(){
				var navsite = jQuery(this).attr('data-navsite');
				loadAJAX(params + getSearchParams(), dst, navsite);
				jQuery("body, html").delay(150).animate({scrollTop:0}, 750, 'swing');
			});
			
			function getSearchParams()
			{	var searchparams = tmp = '';
					searchparams += encodeURIComponent(jQuery('#s_sbeg').val());								//Suchbegriff (param-Name wird in "var params" gesetzt
					searchparams += '&order=' + encodeURIComponent(jQuery('#db-order').attr('data-order'));		//Sortierrichtung asc|desc
				return searchparams;
			}
		});
		</script>

		<!-- Suchbox -->
		<table class="table table-striped addon_search" cellpadding="0" cellspacing="0">
		<tbody>
		<tr>
			<td class="td1" valign="middle"><div class="seocu-result seocu-result-refresh"><i class="rex-icon fa-refresh"></i><?php echo $this->i18n('a1544_bas_list_refreshall'); ?></div></td>
			<td class="td2"><img src="/assets/addons/<?php echo $mypage; ?>/indicator.gif" width="16" height="16" border="0" id="ajax_loading" style="display:none;" /></td>
			<td class="td3">
				<?php echo $this->i18n('a1544_search_keyword'); ?>: 
				<span class="searchholder">
					<input name="s_sbeg" id="s_sbeg" type="text" size="10" maxlength="50" value="<?php echo aFM_maskChar($_SESSION['as_sbeg_seoculist']); ?>" class="sbeg" />
					<a id="s_resetsbeg" title="<?php echo $this->i18n('a1544_search_reset'); ?>"><img src="/assets/addons/<?php echo $mypage; ?>/reset.gif" width="13" height="13" alt="<?php echo $this->i18n('a1544_search_reset'); ?>" border="0" /></a>
				</span>
				<input name="submit" type="button" value="<?php echo $this->i18n('a1544_search_submit'); ?>" class="button" id="s_button" />
			</td>
		</tr>
		</tbody>
		</table>
        


		<!-- Liste -->
        <?php
		$cols = 0; $seo_cols = "";
			if ($config['be_seo_culist_title']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_title').'</th>';
			endif;
			if ($config['be_seo_culist_desc']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_desc').'</th>';
			endif;
			if ($config['be_seo_culist_h1']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_h1').'</th>';
			endif;
			if ($config['be_seo_culist_h2']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_h2').'</th>';
			endif;
			if ($config['be_seo_culist_links'] && $config['be_seo_checks_links']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_links').'</th>';
			endif;
			if ($config['be_seo_culist_words']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_words').'</th>';
			endif;						
			if ($config['be_seo_culist_wdf'] && $config['be_seo_checks_wdf']):
				$cols++;
				$seo_cols .= '<th>'.$this->i18n('a1544_bas_list_wdf').'</th>';
			endif;
		?>        
        
		<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th class="rex-table-id">ID</th>
			<th class="seoculist-nowrap"><?php echo $this->i18n('a1544_bas_list_name'); ?> <a class="db-order db-order-desc" id="db-order" data-order="asc"><i class="rex-icon fa-sort"></i></a></th>

            <?php
			echo $seo_cols;
			
            if ($cols <= 3):
                echo '<th class="seoculist-nowrap">'.$this->i18n('a1544_bas_list_result').'</th>';
            endif;
            ?>
            
            <th>
				<?php
                echo $this->i18n('a1544_bas_list_focuskw');
                
                if ($cols > 3):
                    echo ' / '.$this->i18n('a1544_bas_list_result');
                endif;
                ?>
            </th>
		</tr>
		</thead>

		<tbody id="ajax_jlist">
		<script type="text/javascript">jQuery(function(){ jQuery('#s_button').trigger('click'); });</script>
		</tbody>
		</table>
        
        <div class="modal fade bd-example-modal-lg seocu-modal" id="seocu-modal" tabindex="-1" role="dialog">
        	<div class="modal-dialog modal-dialog-centered" role"document">
            	<div class="modal-content">
                	<div class="modal-header"><div class="modal-title"><?php echo $this->i18n('a1544_seo_modal_title'); ?></div></div>
                    <div class="modal-body seocheckup"><?php echo $this->i18n('a1544_seo_modal_analyze'); ?></div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->i18n('a1544_seo_modal_close'); ?></button></div>
                </div>
            </div>
        </div>

	</div>
</section>