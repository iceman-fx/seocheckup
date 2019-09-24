<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Hilfe
	v1.1.3
	by Falko Müller @ 2019
	package: redaxo5
*/
?>

<section class="rex-page-section">
	<div class="panel panel-default">

		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1544_head_help'); ?></div></header>
        
		<div class="panel-body">
			<div class="rex-docs">
	            <!--
				<div class="rex-docs-sidebar">
                	<nav class="rex-nav-toc">
                    	<ul>
                        	<li><a href="#default">Allgemein</a>
                            <li><a href="#tree">Strukturbaum</a>
                            <li><a href="#seo">SEO-CheckUp</a>
                      </ul>
                    </nav>
        	    </div>
				-->
                
				<div class="rex-docs-content">
                
					<h1>Addon: <?php echo $this->i18n('a1544_title'); ?></h1>

					<p>Mit dieser Erweiterung binden Sie einen SEO-Schnelltest in das Backend von Redaxo ein.<br>
					  Dieser Schnelltest analysiert den jeweiligen Artikel auf wichtige SEO-Optimierungen und informiert Sie über den aktuellen Status.
					</p>
<p>&nbsp;</p>
                  <h2>Erklärung wichtiger Eigenschaften</h2>
              
                    
                    <!-- Allgemein -->
                    <a name="default"></a>
                    <h3>Bereich &quot;<?php echo $this->i18n('a1544_default'); ?>&quot;:</h3>
                  <p>In diesem Bereich können Sie die SEO-Analysen für alle Artikel der Homepage durchgeführen.</p>
                  <p>Zur Analyse des entsprechenden Artikels klicken Sie auf die Aktualisieren-Schaltfläche hinter dem Eingabefeld für das Fokus-Keyword.<br>
                    Sofern ein Fokus-Keyword* hinterlegt wird, umfasst die Analyse zusätzliche Prüfungen zu Verteilung des Suchbegriffes innerhalb der wichtigen SEO-Parameter.</p>
                    <p>Zu den Analysen gehören u.A.: </p>
                  <ul>
                    <li>der Seitentitel (&lt;title&gt;)</li>
                      <li>die Seitenbeschreibung (&lt;meta description&gt;)</li>
                      <li>die H1-Überschrift</li>
                      <li>das Vorkommen des Fokus-Keywords in den einzelnen Bereichen</li>
                      <li>die Keyword-Dichte</li>
                      <li> das Vorhandensein von Bildern</li>
                      <li>  der Flesch-Wert des Seiteninhaltes</li>
                    </ul>
                  <p>&nbsp;</p>
                  <p><strong>Hinweis:</strong><br>
Die Prüfung werden primär mit der Liveversion des Artikels durchgeführt, so dass eine geänderter Artikel für eine korrekte Analyse live geschalten werden muss.</p>
<p>* Fokus-Keyword = der Suchbegriff, auf welchen der jeweilige Artikel optimiert wird/wurde</p>
<p>&nbsp;</p>
                    
                    
                    
                    <!-- SEO-CheckUp -->
                    <a name="seo"></a>
                  <h3>Bereich &quot;<?php echo $this->i18n('a1544_config'); ?>&quot;:</h3>
                    <p>                      Über diesen Bereich können grundlegende Einstellungen für die Analysen definiert werden (z.B. Mindestlänge des Seitentitels), als auch die Anzeige des Schnelltestes in der Editieransicht eines Artikels aktiviert werden. Mit zusätzlichen Optionen kann das Ausgabeverhalten der Anzeige des Schnelltests beeinflusst werden.				</p>
                    <p> Der Schnelltest nutzt dabei die gleichen Prüfungen wie im Bereich &quot;<?php echo $this->i18n('a1544_default'); ?>&quot; und gibt Auskunft über noch durchzuführende Verbesserungen.</p>
			  </div>
			</div>

	  </div>
	</div>
</section>