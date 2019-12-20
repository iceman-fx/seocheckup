<?php
/*
	Redaxo-Addon SEO-CheckUp
	Installation
	v1.3.2
	by Falko Müller @ 2019
	package: redaxo5
*/

//Variablen deklarieren
$mypage = $this->getProperty('package');
$error = "";


//Vorgaben vornehmen
if (!$this->hasConfig()):
	$this->setConfig('config', [
		'be_seo'				=> 'checked',
		'be_seo_opened'			=> '',
		'be_seo_showchecks'		=> '',
		'be_seo_offlinekeywords'=> '',
		'be_seo_offlinearts'	=> '',
		'be_seo_title_min'		=> '50',
		'be_seo_title_max'		=> '65',
		'be_seo_title_words'	=> '6',
		'be_seo_desc_min'		=> '130',
		'be_seo_desc_max'		=> '160',
		'be_seo_desc_words'		=> '12',
		'be_seo_content_words'	=> '300',
		'be_seo_density_min'	=> '2',
		'be_seo_density_max'	=> '4',
		'be_seo_url_max'		=> '55',
		'be_seo_url_depths'		=> '3',
		'be_seo_links'			=> '3',
		'be_seo_removeblock_header'	=> 'checked',
		'be_seo_removeblock_footer'	=> 'checked',
		'be_seo_removeblock_nav'	=> 'checked',
	]);
endif;


//Datenbank-Einträge vornehmen
rex_sql_table::get(rex::getTable('article'))
	->ensureColumn(new rex_sql_column('seocu_keyword', 'varchar(255)'))
	->ensureColumn(new rex_sql_column('seocu_result', 'varchar(100)'))
	->ensureColumn(new rex_sql_column('seocu_data', 'text'))
    ->alter();


//Module anlegen


//Aktionen anlegen


//Templates anlegen
?>