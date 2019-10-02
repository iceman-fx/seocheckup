<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Hilfe
	v1.2
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
                    Sofern ein Fokus-Keyword* hinterlegt wird, umfasst die Analyse zusätzliche Prüfungen zur Verteilung des Suchbegriffes innerhalb von wichtigen SEO-Parametern.</p>
                    <p>Zu den Analysen gehören u.A.: </p>
                  <ul>
                    <li>der Seitentitel (&lt;title&gt;)</li>
                      <li>die Seitenbeschreibung (&lt;meta description&gt;)</li>
                      <li>die H1-Überschrift</li>
                      <li>die Überschriftenstruktur</li>
                      <li>das Vorkommen des Fokus-Keywords in den einzelnen Bereichen</li>
                      <li>die Keyword-Dichte</li>
                      <li> das Vorhandensein von Bildern</li>
                      <li>  der Flesch-Wert des Seiteninhaltes</li>
                    </ul>
                  <p><br>
                    Um die Analyse des jeweiligen Artikels zu starten, klicken Sie auf die Aktualisieren-Schaltfläche hinter dem Eingabefeld für das Fokus-Keyword. <br>
                  Nach Abschluss der Analyse erscheint das Ergebnis in der Übersicht.Weitere Informationen können über die Details-Schaltfläche angezeigt werden.</p>
<p>&nbsp;</p>
                  <p><strong>Hinweis:</strong><br>
Die Prüfung wird mit der Liveversion des Artikels durchgeführt, so dass ein geänderter Artikel für eine korrekte Analyse live geschalten werden muss.</p>
<p>* Fokus-Keyword = der Suchbegriff, auf welchen der jeweilige Artikel optimiert wird/wurde</p>
<p>&nbsp;</p>
                    
                    
                    
                    <!-- SEO-CheckUp -->
                    <a name="seo"></a>
                  <h3>Bereich &quot;<?php echo $this->i18n('a1544_config'); ?>&quot;:</h3>
                    <p>                      Über diesen Bereich können grundlegende Einstellungen für die Analysen definiert werden (z.B. Mindestlänge des Seitentitels), als auch die Anzeige des Schnelltestes in der Editieransicht eines Artikels aktiviert werden. Mit zusätzlichen Optionen kann das Ausgabeverhalten der Anzeige des Schnelltests beeinflusst werden.				</p>
                    <p> Der Schnelltest nutzt dabei die gleichen Prüfungen wie im Bereich &quot;<?php echo $this->i18n('a1544_default'); ?>&quot; und gibt Auskunft über noch durchzuführende Verbesserungen.</p>
                    <p><br>
                    <strong>                    Konfiguration SEO-Prüfungen</strong></p>

                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <th scope="col">Eigenschaft</th>
                        <th scope="col">Erklärung</th>
                    </tr>
                      <tr>
                        <td valign="top"><strong>Content aufbereiten</strong></td>
                        <td valign="top">Festlegung des Umfanges des zu prüfenden Quellcodes des Contentbereiches (&lt;body&gt;...&lt;/body&gt;)</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Anzahl Zeichen Seitentitel</strong></td>
                        <td valign="top">Bereich einer Anzahl von Zeichen  für einen erfolgreichen Test</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Anzahl Zeichen Seitenbeschreibung</strong></td>
                        <td valign="top">Bereich einer Anzahl von Zeichen  für einen erfolgreichen Test</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Anzahl Wörter Seitentitel</strong></td>
                        <td valign="top">Minimale Anzahl an Wörtern   für einen erfolgreichen Test</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Anzahl Wörter Seitenbeschreibung</strong></td>
                        <td valign="top">Minimale Anzahl an Wörtern   für einen erfolgreichen Test</td>
                      </tr>
                        <tr>
                          <td valign="top"><strong>Anzahl Wörter Content</strong></td>
                          <td valign="top">Minimale Anzahl an Wörtern   für einen erfolgreichen Test</td>
                        </tr>
                        <tr>
                          <td width="200" valign="top"><strong>Anzahl interne Verlinkungen</strong></td>
                          <td valign="top">Minimale Anzahl von Verlinkungen zu anderen CMS-Artikeln für einen erfolgreichen Test</td>
                        </tr>
                        <tr>
                          <td valign="top"><strong>Länge der URL</strong></td>
                          <td valign="top">Maximale Anzahl von Zeichen für einen erfolgreichen Test</td>
                        </tr>
                        <tr>
                          <td valign="top"><strong>URL-Verzeichnistiefe</strong></td>
                          <td valign="top">Maximale Anzahl an Verzeichnisebenen für einen erfolgreichen Test (Beispiel: /de/kategorie/index.html = 2) </td>
                        </tr>
                        <tr>
                          <td valign="top"><strong>Keyword-Dichte (%)</strong></td>
                          <td valign="top">Bereich in Prozent für einen erfolgreichen Test</td>
                        </tr>
                        <tr>
                          <td valign="top"><strong>Keyword in Offline-Artikeln</strong></td>
                          <td valign="top">Nutzung des Fokus-Keywords auch in Offline-Artikel prüfen</td>
                        </tr>
                  </table>
                    
                                        
	          </div>
			</div>

	  </div>
	</div>
</section>