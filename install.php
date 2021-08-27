<?php
/*
	Redaxo-Addon SEO-CheckUp
	Installation
	v1.5
	by Falko Müller @ 2019-2021
	package: redaxo5
*/

//Variablen deklarieren
$mypage = $this->getProperty('package');
$error = "";


//Vorgaben vornehmen
$stopwords = rex_file::get(rex_addon::get($mypage)->getPath('data/stopwords.txt'));
	$stopwords = trim($stopwords);

if (!$this->hasConfig()):
	$this->setConfig('config', [
		'be_seo'					=> 'checked',
		'be_seo_opened'				=> '',
		'be_seo_showchecks'			=> '',
		'be_seo_sidebar_wdf'		=> 'checked',
		'be_seo_sidebar_snippet'	=> 'checked',
		'be_seo_sidebar_priority'	=> '',
		'be_seo_offlinekeywords'	=> '',
		'be_seo_offlinearts'		=> '',
		'be_seo_culist_count'		=> '20',
		'be_seo_culist_title'		=> 'checked',
		'be_seo_culist_desc'		=> 'checked',
		'be_seo_culist_snippet'		=> '',
		'be_seo_culist_h1'			=> '',
		'be_seo_culist_h2'			=> '',
		'be_seo_culist_links'		=> '',
		'be_seo_culist_words'		=> '',
		'be_seo_culist_wdf'			=> '',		
		'be_seo_title_min'			=> '50',
		'be_seo_title_max'			=> '65',
		'be_seo_title_words'		=> '6',
		'be_seo_desc_min'			=> '130',
		'be_seo_desc_max'			=> '160',
		'be_seo_desc_words'			=> '12',
		'be_seo_content_words'		=> '300',
		'be_seo_content_words_dec'	=> '0',
		'be_seo_density_min'		=> '2',
		'be_seo_density_max'		=> '4',
		'be_seo_url_max'			=> '55',
		'be_seo_url_depths'			=> '3',
		'be_seo_links'				=> '3',
		'be_seo_hyphenator'			=> '',
		'be_seo_removeblock_header'	=> '',
		'be_seo_removeblock_footer'	=> '',
		'be_seo_removeblock_nav'	=> '',
		'be_seo_checks_selection'	=> '',
		'be_seo_checks_titledesc'	=> 'checked',
		'be_seo_checks_opengraph'	=> 'checked',
		'be_seo_checks_url'			=> 'checked',
		'be_seo_checks_header'		=> 'checked',
		'be_seo_checks_content'		=> 'checked',
		'be_seo_checks_links'		=> 'checked',
		'be_seo_checks_images'		=> 'checked',
		'be_seo_checks_density'		=> 'checked',
		'be_seo_checks_wdf'			=> 'checked',
		'be_seo_checks_flesch'		=> 'checked',
		'be_seo_wdf_stopwords'		=> $stopwords,
		'be_seo_wdf_countwords'		=> '20',
		'be_seo_wdf_skipshortwords'	=> '',
	]);
endif;


//Datenbank-Spalten anlegen, sofern noch nicht verfügbar
rex_sql_table::get(rex::getTable('article'))
	->ensureColumn(new rex_sql_column('seocu_keyword', 'varchar(255)'))
	->ensureColumn(new rex_sql_column('seocu_result', 'varchar(100)'))
	->ensureColumn(new rex_sql_column('seocu_data', 'text'))
	->ensureColumn(new rex_sql_column('seocu_updatedate', 'varchar(50)'))
    ->alter();


//Module anlegen


//Aktionen anlegen


//Templates anlegen
?>