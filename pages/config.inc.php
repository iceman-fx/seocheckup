<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Einstellungen (config)
	v1.1.3
	by Falko Müller @ 2019
	package: redaxo5
*/

//Variablen deklarieren
$form_error = 0;

//Formular dieser Seite verarbeiten
if ($func == "save" && isset($_POST['submit'])):
	//Konfig speichern
	$newCfg = $this->getConfig('config');												//alte Config laden

	$newCfg = array_merge($newCfg, [													//neue Werte der Standardfelder hinzufügen
		'be_seo'				=> rex_post('be_seo'),
		'be_seo_opened'			=> rex_post('be_seo_opened'),
		'be_seo_showchecks'		=> rex_post('be_seo_showchecks'),
		'be_seo_offlinekeywords'=> rex_post('be_seo_offlinekeywords'),
		'be_seo_title_min'		=> rex_post('be_seo_title_min', 'int'),
		'be_seo_title_max'		=> rex_post('be_seo_title_max', 'int'),		
		'be_seo_title_words'	=> rex_post('be_seo_title_words', 'int'),
		'be_seo_desc_min'		=> rex_post('be_seo_desc_min', 'int'),
		'be_seo_desc_max'		=> rex_post('be_seo_desc_max', 'int'),
		'be_seo_desc_words'		=> rex_post('be_seo_desc_words', 'int'),
		'be_seo_content_words'	=> rex_post('be_seo_content_words', 'int'),
		'be_seo_density_min'	=> rex_post('be_seo_density_min', 'int'),
		'be_seo_density_max'	=> rex_post('be_seo_density_max', 'int'),		
		'be_seo_removeblock_header'	=> rex_post('be_seo_removeblock_header'),
		'be_seo_removeblock_footer'	=> rex_post('be_seo_removeblock_footer'),
		'be_seo_removeblock_nav'	=> rex_post('be_seo_removeblock_nav'),		
	]);

	$res = $this->setConfig('config', $newCfg);											//Config speichern (ersetzt komplett die alte Config)

	//Rückmeldung
	echo ($res) ? rex_view::info($this->i18n('a1544_settings_saved')) : rex_view::warning($this->i18n('a1544_error'));

	//reload Konfig
	$config = $this->getConfig('config');
		$config = aFM_maskArray($config);
endif;
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
                <dt><label for=""><?php echo $this->i18n('a1544_config_seo_opened'); ?></label></dt>
                <dd>
                    <div class="checkbox">
                    <label for="be_seo_opened">
                        <input name="be_seo_opened" type="checkbox" id="be_seo_opened" value="checked" <?php echo @$config['be_seo_opened']; ?> /> <?php echo $this->i18n('a1544_yes').', '.$this->i18n('a1544_config_seo_opened_info'); ?>
                    </label>
                    </div>
                </dd>
            </dl>
            
            
            <dl class="rex-form-group form-group"><dt></dt></dl>
            
            <legend><?php echo $this->i18n('a1544_subheader_config2'); ?></legend>


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


            <script type="text/javascript">jQuery('.hiddencontent').hide();</script>
                    
            
        </div>
        
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