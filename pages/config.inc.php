<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Einstellungen (config)
	v1.4
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//Variablen deklarieren
$form_error = 0;

//Formular dieser Seite verarbeiten
if ($func == "save" && isset($_POST['submit'])):
	//Konfig speichern
	$newCfg = $this->getConfig('config');												//alte Config laden

	$newCfg = array_merge($newCfg, [													//neue Werte der Standardfelder hinzufügen
		'be_seo'					=> rex_post('be_seo'),
		'be_seo_opened'				=> rex_post('be_seo_opened'),
		'be_seo_showchecks'			=> rex_post('be_seo_showchecks'),
		'be_seo_sidebar_wdf'		=> rex_post('be_seo_sidebar_wdf'),
		'be_seo_sidebar_snippet'	=> rex_post('be_seo_sidebar_snippet'),
		'be_seo_offlinekeywords'	=> rex_post('be_seo_offlinekeywords'),
		'be_seo_offlinearts'		=> rex_post('be_seo_offlinearts'),
		'be_seo_culist_count'		=> rex_post('be_seo_culist_count', 'int'),
		'be_seo_culist_title'		=> rex_post('be_seo_culist_title'),
		'be_seo_culist_desc'		=> rex_post('be_seo_culist_desc'),
		'be_seo_culist_snippet'		=> rex_post('be_seo_culist_snippet'),
		'be_seo_culist_h1'			=> rex_post('be_seo_culist_h1'),
		'be_seo_culist_h2'			=> rex_post('be_seo_culist_h2'),
		'be_seo_culist_links'		=> rex_post('be_seo_culist_links'),
		'be_seo_culist_words'		=> rex_post('be_seo_culist_words'),
		'be_seo_culist_wdf'			=> rex_post('be_seo_culist_wdf'),
		'be_seo_title_min'			=> rex_post('be_seo_title_min', 'int'),
		'be_seo_title_max'			=> rex_post('be_seo_title_max', 'int'),		
		'be_seo_title_words'		=> rex_post('be_seo_title_words', 'int'),
		'be_seo_desc_min'			=> rex_post('be_seo_desc_min', 'int'),
		'be_seo_desc_max'			=> rex_post('be_seo_desc_max', 'int'),
		'be_seo_desc_words'			=> rex_post('be_seo_desc_words', 'int'),
		'be_seo_content_words'		=> rex_post('be_seo_content_words', 'int'),
		'be_seo_density_min'		=> rex_post('be_seo_density_min', 'int'),
		'be_seo_density_max'		=> rex_post('be_seo_density_max', 'int'),
		'be_seo_url_max'			=> rex_post('be_seo_url_max', 'int'),
		'be_seo_url_depths'			=> rex_post('be_seo_url_depths', 'int'),
		'be_seo_links'				=> rex_post('be_seo_links', 'int'),
		'be_seo_hyphenator'			=> rex_post('be_seo_hyphenator'),
		'be_seo_removeblock_header'	=> rex_post('be_seo_removeblock_header'),
		'be_seo_removeblock_footer'	=> rex_post('be_seo_removeblock_footer'),
		'be_seo_removeblock_nav'	=> rex_post('be_seo_removeblock_nav'),
		'be_seo_checks_selection'	=> rex_post('be_seo_checks_selection'),
		'be_seo_checks_titledesc'	=> rex_post('be_seo_checks_titledesc'),
		'be_seo_checks_opengraph'	=> rex_post('be_seo_checks_opengraph'),
		'be_seo_checks_url'			=> rex_post('be_seo_checks_url'),
		'be_seo_checks_header'		=> rex_post('be_seo_checks_header'),
		'be_seo_checks_content'		=> rex_post('be_seo_checks_content'),
		'be_seo_checks_links'		=> rex_post('be_seo_checks_links'),
		'be_seo_checks_images'		=> rex_post('be_seo_checks_images'),
		'be_seo_checks_density'		=> rex_post('be_seo_checks_density'),
		'be_seo_checks_wdf'			=> rex_post('be_seo_checks_wdf'),
		'be_seo_checks_flesch'		=> rex_post('be_seo_checks_flesch'),
		'be_seo_wdf_stopwords'		=> rex_post('be_seo_wdf_stopwords'),
		'be_seo_wdf_countwords'		=> rex_post('be_seo_wdf_countwords'),
		'be_seo_wdf_skipshortwords'	=> rex_post('be_seo_wdf_skipshortwords'),
	]);

	$res = $this->setConfig('config', $newCfg);											//Config speichern (ersetzt komplett die alte Config)

	//Rückmeldung
	echo ($res) ? rex_view::info($this->i18n('a1544_settings_saved')) : rex_view::warning($this->i18n('a1544_error'));
endif;


//reload Konfig
$config = $this->getConfig('config');
	$config = aFM_maskArray($config);
?>


<script type="text/javascript">setTimeout(function() { jQuery('.alert-info').fadeOut(); }, 5000);</script>

<form action="index.php?page=<?php echo $page; ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="subpage" value="<?php echo $subpage; ?>" />
<input type="hidden" name="func" value="save" />

<section class="rex-page-section">
    <div class="panel panel-edit">
    
		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1544_head_config'); ?></div></header>
        
		<div class="panel-body">

			<legend><?php echo $this->i18n('a1544_subheader_config1'); ?></legend>
                 
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo'); ?></label></dt>
                <dd>
                    <div class="checkbox">
                    <label for="be_seo">
                        <input name="be_seo" type="checkbox" id="be_seo" value="checked" <?php echo @$config['be_seo']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_info'); ?>
                    </label>
                    </div>
                </dd>
            </dl>
             
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_opened'); ?></label></dt>
                <dd>
                    <div class="checkbox">
                    <label for="be_seo_opened">
                        <input name="be_seo_opened" type="checkbox" id="be_seo_opened" value="checked" <?php echo @$config['be_seo_opened']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_opened_info'); ?>
                    </label>
                    </div>
                </dd>
            </dl>

            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_showchecks'); ?></label></dt>
                <dd>
                    <div class="radio">
                    <label for="seomode1">
                        <input name="be_seo_showchecks" type="radio" value="none" id="seomode1" <?php echo (@$config['be_seo_showchecks'] != "checked") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1544_config_seo_showchecks_info1'); ?>
                    </label>
                    <label for="seomode2">
                        <input name="be_seo_showchecks" type="radio" value="checked" id="seomode2" <?php echo (@$config['be_seo_showchecks'] == "checked") ? 'checked' : ''; ?> /> <?php echo $this->i18n('a1544_config_seo_showchecks_info2'); ?>
                    </label>
                    </div>
                </dd>
            </dl>
            
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_culist_cols'); ?></label></dt>
                <dd>

                    <div class="checkbox">
                    <label for="be_seo_sidebar_wdf">
                        <input name="be_seo_sidebar_wdf" type="checkbox" id="be_seo_sidebar_wdf" value="checked" <?php echo @$config['be_seo_sidebar_wdf']; ?> /> <?php echo $this->i18n('a1544_config_seo_sidebar_wdf').' '.$this->i18n('a1544_config_seo_culist_checkmustactive'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_sidebar_snippet">
                        <input name="be_seo_sidebar_snippet" type="checkbox" id="be_seo_sidebar_snippet" value="checked" <?php echo @$config['be_seo_sidebar_snippet']; ?> /> <?php echo $this->i18n('a1544_config_seo_sidebar_snippet'); ?>
                    </label>
                    </div>
                </dd>
            </dl>
            

            <dl class="rex-form-group form-group"><dt></dt></dl>
            
            
			<legend><?php echo $this->i18n('a1544_subheader_config3'); ?></legend>

            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_culist_count'); ?></label></dt>
                <dd>
                    <select size="1" name="be_seo_culist_count" class="form-control" id="be_seo_culist_count">
                        <?php
						$config['be_seo_culist_count'] = (@$config['be_seo_culist_count'] == '') ? 25 : intval(@$config['be_seo_culist_count']);		//Fallback zu alten Versionen vor 1.4
                        for ($i=10; $i<=30; $i=$i+5):
                            $sel = ($config['be_seo_culist_count'] == $i) ? 'selected' : '';
                            echo '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
                        endfor;
                        ?>
                    </select>
                </dd>
            </dl>

            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_offlinearts'); ?></label></dt>
                <dd>
                    <div class="checkbox">
                    <label for="be_seo_offlinearts">
                        <input name="be_seo_offlinearts" type="checkbox" id="be_seo_offlinearts" value="checked" <?php echo @$config['be_seo_offlinearts']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_offlinearts_info'); ?>
                    </label>
                    </div>
                </dd>
            </dl>
            
            <dl class="rex-form-group form-group">
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_culist_cols'); ?></label></dt>
                <dd>
                    <div class="checkbox">
                    <label for="be_seo_culist_title">
                        <input name="be_seo_culist_title" type="checkbox" id="be_seo_culist_title" value="checked" <?php echo @$config['be_seo_culist_title']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_title'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_desc">
                        <input name="be_seo_culist_desc" type="checkbox" id="be_seo_culist_desc" value="checked" <?php echo @$config['be_seo_culist_desc']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_desc'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_h1">
                        <input name="be_seo_culist_h1" type="checkbox" id="be_seo_culist_h1" value="checked" <?php echo @$config['be_seo_culist_h1']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_h1'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_h2">
                        <input name="be_seo_culist_h2" type="checkbox" id="be_seo_culist_h2" value="checked" <?php echo @$config['be_seo_culist_h2']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_h2'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_links">
                        <input name="be_seo_culist_links" type="checkbox" id="be_seo_culist_links" value="checked" <?php echo @$config['be_seo_culist_links']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_links').' '.$this->i18n('a1544_config_seo_culist_checkmustactive'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_words">
                        <input name="be_seo_culist_words" type="checkbox" id="be_seo_culist_words" value="checked" <?php echo @$config['be_seo_culist_words']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_words'); ?>
                    </label>
                    </div>

                    <div class="checkbox">
                    <label for="be_seo_culist_wdf">
                        <input name="be_seo_culist_wdf" type="checkbox" id="be_seo_culist_wdf" value="checked" <?php echo @$config['be_seo_culist_wdf']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_wdf').' '.$this->i18n('a1544_config_seo_culist_checkmustactive'); ?>
                    </label>
                    </div>

					<!--	macht nicht viel Sinn und belegt viel Platz
                    <div class="checkbox">
                    <label for="be_seo_culist_snippet">
                        <input name="be_seo_culist_snippet" type="checkbox" id="be_seo_culist_snippet" value="checked" <?php echo @$config['be_seo_culist_snippet']; ?> /> <?php echo $this->i18n('a1544_config_seo_culist_cols_snippet'); ?>
                    </label>
                    </div>
                    -->
                </dd>
            </dl>            
            
            
            <dl class="rex-form-group form-group"><dt></dt></dl>
            
            
            <legend><?php echo $this->i18n('a1544_subheader_config2'); ?> &nbsp; (<a href="javascript:;" onclick="jQuery('#options3').toggle();"><?php echo $this->i18n('a1544_showbox'); ?></a>)</legend>
            <div class="hiddencontent" id="options3">                
                
                <!-- Auswahl Prüfungen -->
                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_checks_selection'); ?></label></dt>
                    <dd>                    
                        <div class="radio">
                        <label for="seochecks1">
                            <input name="be_seo_checks_selection" type="radio" value="none" id="seochecks1" <?php echo (@$config['be_seo_checks_selection'] != "checked") ? 'checked' : ''; ?> data-fid="all" /> <?php echo $this->i18n('a1544_config_seo_checks_selection_info1'); ?>
                        </label>
                        <label for="seochecks2">
                            <input name="be_seo_checks_selection" type="radio" value="checked" id="seochecks2" <?php echo (@$config['be_seo_checks_selection'] == "checked") ? 'checked' : ''; ?> data-fid="individual" /> <?php echo $this->i18n('a1544_config_seo_checks_selection_info2'); ?>
                        </label>
                        </div>
                    </dd>
                </dl>


                <dl class="rex-form-group form-group hiddencontent" id="checks_selection">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_checks_list'); ?></label></dt>
                    <dd>
                        <div class="checkbox">
                        <label for="be_seo_checks_titledesc">
                            <input name="be_seo_checks_titledesc" type="checkbox" id="be_seo_checks_titledesc" value="checked" <?php echo @$config['be_seo_checks_titledesc']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_titledesc'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_opengraph">
                            <input name="be_seo_checks_opengraph" type="checkbox" id="be_seo_checks_opengraph" value="checked" <?php echo @$config['be_seo_checks_opengraph']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_opengraph'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_url">
                            <input name="be_seo_checks_url" type="checkbox" id="be_seo_checks_url" value="checked" <?php echo @$config['be_seo_checks_url']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_url'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_header">
                            <input name="be_seo_checks_header" type="checkbox" id="be_seo_checks_header" value="checked" <?php echo @$config['be_seo_checks_header']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_header'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_content">
                            <input name="be_seo_checks_content" type="checkbox" id="be_seo_checks_content" value="checked" <?php echo @$config['be_seo_checks_content']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_content'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_links">
                            <input name="be_seo_checks_links" type="checkbox" id="be_seo_checks_links" value="checked" <?php echo @$config['be_seo_checks_links']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_links'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_images">
                            <input name="be_seo_checks_images" type="checkbox" id="be_seo_checks_images" value="checked" <?php echo @$config['be_seo_checks_images']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_images'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_density">
                            <input name="be_seo_checks_density" type="checkbox" id="be_seo_checks_density" value="checked" <?php echo @$config['be_seo_checks_density']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_density'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_wdf">
                            <input name="be_seo_checks_wdf" type="checkbox" id="be_seo_checks_wdf" value="checked" <?php echo @$config['be_seo_checks_wdf']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_wdf'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_checks_flesch">
                            <input name="be_seo_checks_flesch" type="checkbox" id="be_seo_checks_flesch" value="checked" <?php echo @$config['be_seo_checks_flesch']; ?> /> <?php echo $this->i18n('a1544_config_seo_checks_flesch'); ?>
                        </label>
                        </div>
                    </dd>
                </dl>
                
                            
                <dl class="rex-form-group form-group"><dt></dt></dl>
            

                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_removeblock'); ?></label></dt>
                    <dd>
                        <div class="checkbox">
                        <label for="be_seo_removeblock_header">
                            <input name="be_seo_removeblock_header" type="checkbox" id="be_seo_removeblock_header" value="checked" <?php echo @$config['be_seo_removeblock_header']; ?> /> <?php echo $this->i18n('a1544_config_seo_removeblock_header'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_removeblock_footer">
                            <input name="be_seo_removeblock_footer" type="checkbox" id="be_seo_removeblock_footer" value="checked" <?php echo @$config['be_seo_removeblock_footer']; ?> /> <?php echo $this->i18n('a1544_config_seo_removeblock_footer'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_removeblock_nav">
                            <input name="be_seo_removeblock_nav" type="checkbox" id="be_seo_removeblock_nav" value="checked" <?php echo @$config['be_seo_removeblock_nav']; ?> /> <?php echo $this->i18n('a1544_config_seo_removeblock_nav'); ?>
                        </label>
                        </div>
    
                        <div class="checkbox">
                        <label for="be_seo_hyphenator">
                            <input name="be_seo_hyphenator" type="checkbox" id="be_seo_hyphenator" value="checked" <?php echo @$config['be_seo_hyphenator']; ?> /> <?php echo $this->i18n('a1544_config_seo_hyphenator'); ?>
                        </label>
                        </div>
                    </dd>
                </dl>
                
                
                <dl class="rex-form-group form-group"><dt></dt></dl>
                
                
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_title_min"><?php echo $this->i18n('a1544_config_seo_title_chars'); ?></label></dt>
                    <dd>
                        <div class="form_column">
                            <div class="input-group">                        
                                <input type="text" size="25" name="be_seo_title_min" id="be_seo_title_min" value="<?php echo @$config['be_seo_title_min']; ?>" maxlength="3" class="form-control" placeholder="min" />
                            </div>
                        </div>
                        
                        <div class="form_column form_column-spacer">-</div>
                        
                        <div class="form_column">
                            <div class="input-group">
                                <input type="text" size="25" name="be_seo_title_max" id="be_seo_title_max" value="<?php echo @$config['be_seo_title_max']; ?>" maxlength="3" class="form-control" placeholder="max" />
                            </div>
                        </div>
    
                    </dd>
                </dl>
               
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_desc_min"><?php echo $this->i18n('a1544_config_seo_desc_chars'); ?></label></dt>
                    <dd>
                        <div class="form_column">
                            <div class="input-group">                        
                                <input type="text" size="25" name="be_seo_desc_min" id="be_seo_desc_min" value="<?php echo @$config['be_seo_desc_min']; ?>" maxlength="3" class="form-control" placeholder="min" />
                            </div>
                        </div>
                        
                        <div class="form_column form_column-spacer">-</div>
                        
                        <div class="form_column">
                            <div class="input-group">
                                <input type="text" size="25" name="be_seo_desc_max" id="be_seo_desc_max" value="<?php echo @$config['be_seo_desc_max']; ?>" maxlength="3" class="form-control" placeholder="max" />
                            </div>
                        </div>
    
                    </dd>
                </dl>
                 
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_title_words"><?php echo $this->i18n('a1544_config_seo_title_words'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_title_words" id="be_seo_title_words" value="<?php echo @$config['be_seo_title_words']; ?>" maxlength="2" class="form-control" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_desc_words"><?php echo $this->i18n('a1544_config_seo_desc_words'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_desc_words" id="be_seo_desc_words" value="<?php echo @$config['be_seo_desc_words']; ?>" maxlength="2" class="form-control" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_content_words"><?php echo $this->i18n('a1544_config_seo_content_words'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_content_words" id="be_seo_content_words" value="<?php echo @$config['be_seo_content_words']; ?>" maxlength="3" class="form-control" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_links"><?php echo $this->i18n('a1544_config_seo_links'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_links" id="be_seo_links" value="<?php echo @$config['be_seo_links']; ?>" maxlength="2" class="form-control" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_url_max"><?php echo $this->i18n('a1544_config_seo_url_max'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_url_max" id="be_seo_url_max" value="<?php echo @$config['be_seo_url_max']; ?>" maxlength="3" class="form-control" placeholder="max" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_url_depths"><?php echo $this->i18n('a1544_config_seo_url_depths'); ?></label></dt>
                    <dd>
                        <input type="text" size="25" name="be_seo_url_depths" id="be_seo_url_depths" value="<?php echo @$config['be_seo_url_depths']; ?>" maxlength="2" class="form-control" placeholder="max" />
                    </dd>
                </dl>
                
            
                <dl class="rex-form-group form-group">
                    <dt><label for="be_seo_density_min"><?php echo $this->i18n('a1544_config_seo_density_range'); ?></label></dt>
                    <dd>
                        <div class="form_column">
                            <div class="input-group">                        
                                <input type="text" size="25" name="be_seo_density_min" id="be_seo_density_min" value="<?php echo @$config['be_seo_density_min']; ?>" maxlength="2" class="form-control" placeholder="min" />
                            </div>
                        </div>
                        
                        <div class="form_column form_column-spacer">-</div>
                        
                        <div class="form_column">
                            <div class="input-group">
                                <input type="text" size="25" name="be_seo_density_max" id="be_seo_density_max" value="<?php echo @$config['be_seo_density_max']; ?>" maxlength="2" class="form-control" placeholder="max" />
                            </div>
                        </div>
    
                    </dd>
                </dl>
    
                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_offlinekeywords'); ?></label></dt>
                    <dd>
                        <div class="checkbox">
                        <label for="be_seo_offlinekeywords">
                            <input name="be_seo_offlinekeywords" type="checkbox" id="be_seo_offlinekeywords" value="checked" <?php echo @$config['be_seo_offlinekeywords']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_offlinekeywords_info'); ?>
                        </label>
                        </div>
                    </dd>
                </dl>

			</div>
            
            
            <dl class="rex-form-group form-group"><dt></dt></dl>
            
            
            <!-- Einstellungen WDF -->
            <legend><?php echo $this->i18n('a1544_subheader_config4'); ?> &nbsp; (<a href="javascript:;" onclick="jQuery('#options4').toggle();"><?php echo $this->i18n('a1544_showbox'); ?></a>)</legend>
            <div class="hiddencontent" id="options4">                
                
                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_wdf_stopwords'); ?></label></dt>
                    <dd>
                    	<?php
						//Stopwords-Basis-Set nach Update auf 1.4 einladen
						$stopwords = rex_file::get(rex_addon::get($mypage)->getPath('data/stopwords.txt'));
							$stopwords = trim($stopwords);

						$config['be_seo_wdf_stopwords'] = (!isset($config['be_seo_wdf_stopwords'])) ? $stopwords : $config['be_seo_wdf_stopwords'];
						?>
                        <textarea name="be_seo_wdf_stopwords" rows="10" id="be_seo_wdf_stopwords" class="form-control"><?php echo @$config['be_seo_wdf_stopwords']; ?></textarea>
                        <span class="infoblock"><?php echo rex_i18n::rawmsg('a1544_config_seo_wdf_stopwords_info'); ?></span>
                    </dd>
                </dl>


                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_wdf_countwords'); ?></label></dt>
                    <dd>
                        <select size="1" name="be_seo_wdf_countwords" class="form-control" id="be_seo_wdf_countwords">
                        	<?php
							$config['be_seo_wdf_countwords'] = (@$config['be_seo_wdf_countwords'] == '') ? 20 : intval(@$config['be_seo_wdf_countwords']);		//Fallback zu alten Versionen vor 1.4
							for ($i=10; $i<=30; $i=$i+10):
								$sel = (@$config['be_seo_wdf_countwords'] == $i) ? 'selected' : '';
								echo '<option value="'.$i.'" '.$sel.'>'.$i.'</option>';
							endfor;
							?>
                        </select>
                    </dd>
                </dl>
                

                <dl class="rex-form-group form-group">
                    <dt><label for=""><?php echo $this->i18n('a1544_config_seo_wdf_skipshortwords'); ?></label></dt>
                    <dd>
                        <div class="checkbox">
                        <label for="be_seo_wdf_skipshortwords">
                            <input name="be_seo_wdf_skipshortwords" type="checkbox" id="be_seo_wdf_skipshortwords" value="checked" <?php echo @$config['be_seo_wdf_skipshortwords']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_wdf_skipshortwords_info'); ?>
                        </label>
                        </div>
                    </dd>
                </dl>

			</div>
            
        </div>
        
        
		<script type="text/javascript">
		jQuery('.hiddencontent').hide();
		
        $("input[name=be_seo_checks_selection]").click(function(){ 
			var dst = $(this).attr('data-fid'); 
			var box = $('#checks_selection');
			
			if (dst == 'individual'){ box.show(); } else { box.hide(); }
		});
        $("input[name=be_seo_checks_selection]:checked").trigger('click');
        </script>
        
        
        <footer class="panel-footer">
        	<div class="rex-form-panel-footer">
            	<div class="btn-toolbar">
                	<input class="btn btn-save rex-form-aligned" type="submit" name="submit" title="<?php echo $this->i18n('a1544_save'); ?>" value="<?php echo $this->i18n('a1544_save'); ?>" />
                </div>
			</div>
		</footer>
        
	</div>
</section>

</form>