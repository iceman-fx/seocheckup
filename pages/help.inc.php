<?php
/*
	Redaxo-Addon SEO-CheckUp
	Verwaltung: Hilfe
	v1.4
	by Falko Müller @ 2019-2021
	package: redaxo5
*/
?>

<style>
.faq { margin: 0px !important; cursor: pointer; }
.faq + div { margin: 0px 0px 15px; }
</style>

<section class="rex-page-section">
	<div class="panel panel-default">

		<header class="panel-heading"><div class="panel-title"><?php echo $this->i18n('a1544_head_help'); ?></div></header>
        
		<div class="panel-body">
			<div class="rex-docs">
				<div class="rex-docs-sidebar">
                	<nav class="rex-nav-toc">
                    	<ul>
                        	<li><a href="#default"><?php echo $this->i18n('a1544_default'); ?></a>
                            <li><a href="#seo"><?php echo $this->i18n('a1544_config'); ?></a>
                            <li><a href="#faq">FAQ</a>
                      </ul>
                    </nav>
        	    </div>
                
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
                    <p>Zu den Analysen gehören u.A. (je nach Konfiguration): </p>
                  <ul>
                    <li>der Seitentitel (&lt;title&gt;)</li>
                      <li>die Seitenbeschreibung (&lt;meta description&gt;)</li>
                      <li>die H1-Überschrift</li>
                      <li>die Überschriftenstruktur</li>
                      <li>das Vorkommen des Fokus-Keywords in den einzelnen Bereichen</li>
                      <li>die Keyword-Dichte und die WDF-Kalkulation</li>
                      <li>das Vorhandensein von Bildern und Verlinkungen</li>
                      <li>der Flesch-Wert des Seiteninhaltes</li>
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
                    <p>                      Über diesen Bereich können grundlegende Einstellungen für die Analysen definiert werden (z.B. Mindestlänge des Seitentitels), als auch die Anzeige des Schnelltestes in der Editieransicht eines Artikels aktiviert werden. Mit zusätzlichen Optionen kann das Ausgabeverhalten der Anzeige in der Sidebar und im <?php echo $this->i18n('a1544_default'); ?> beeinflusst werden.				</p>
                    <p> Der Schnelltest nutzt  die gleichen Prüfungen wie im Bereich &quot;<?php echo $this->i18n('a1544_default'); ?>&quot; und gibt Auskunft über noch durchzuführende Verbesserungen.</p>
                    <p><br>
                    <strong>                    Konfiguration SEO-Prüfungen</strong></p>

                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <th scope="col">Einstellung</th>
                        <th scope="col">Erklärung</th>
                    </tr>
                      <tr>
                        <td valign="top"><strong>Prüfungen &amp; Analysen</strong></td>
                        <td valign="top">Auswahl ob alle Prüfungen oder nur einzelne Gruppen durchgeführt werden sollen</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Content aufbereiten</strong></td>
                        <td valign="top">Festlegung des prüfenden Quellcode-Umfanges  des Contentbereiches (&lt;body&gt;...&lt;/body&gt;).<br>
                        Des Weiteren kann hier die Hyphenator-Trennung rückgängig gemacht werden.</td>
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
                  <p><br>
                  <strong>                    Konfiguration WDF-Kalkulator</strong></p>

                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <th width="200" scope="col">Einstellung</th>
                        <th scope="col">Erklärung</th>
                    </tr>
                      <tr>
                        <td valign="top"><strong>Stopwörter</strong></td>
                        <td valign="top">Zeilenweise Hinterlegung Ihrer gewünschten Stopwörter, welche nicht in die Kalkulation einfließen sollen.<br>
                          <br>
                          Hinweis: Bei der Installation des Addons werden bereits erste Stopwörter eingefügt, welche jederzeit geändert werden können.</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Anzahl Wörter in WDF-Übersicht</strong></td>
                        <td valign="top">Anzahl darzustellender Top-Keywords in der WDF-Übersicht.</td>
                      </tr>
                      <tr>
                        <td valign="top"><strong>Kurze Wörter auslassen</strong></td>
                        <td valign="top">Kurze Wörter (kleiner 4 Zeichen) aus der WDF-Kalkulation auslassen</td>
                      </tr>
                  </table>
<p>&nbsp;</p>                   
                    
                    
<!-- SEO-CheckUp -->
                    <a name="faq"></a>
                    <h3>FAQ:</h3>
                  
                    <p class="faq text-danger" data-toggle="collapse" data-target="#f001"><span class="caret"></span> Es wird keine H1-Überschrift gefunden, obwohl diese vorhanden ist.</p>
                    <div id="f001" class="collapse">Die meisten Webseiten legen Ihre H1-Überschrift im Contentbereich ab.<br>
                    Wenn sich die H1-Überschrift nun aber z.B. im Header befindet, dann sollte die Option zum Entfernen der &lt;header&gt;-Blöcke in den Einstellungen deaktiviert werden.</div>
                    

                    <p class="faq text-danger" data-toggle="collapse" data-target="#f002"><span class="caret"></span> Es wird eine rex_socket Fehlermeldung ausgegeben.</p>
                    <div id="f002" class="collapse">Diese Meldung erscheint immer dann, wenn ein Fehler beim Abrufen der Liveversion aufgetreten ist. Gründe können z.B. Probleme bei der Erreichbarkeit der Seite sein.<br>
In den meisten Fällen funktioniert der Abruf nach einer kurzen Zeit wieder wie gewohnt.</div>


                    <p class="faq text-danger" data-toggle="collapse" data-target="#f003"><span class="caret"></span> Es werden keine oder falsche Ergebnisse angezeigt, wenn sich die Seite im Wartungsmodus (maintenance) befindet.</p>
                    <div id="f003" class="collapse">Durch den Wartungsmodus werden alle öffentlichen Anfragen auf eine Wartungsseite umgelenkt. Der Aufruf der tatsächlichen Seite ist daher nicht möglich.<br>
                    Auch eine Anmeldung im Redaxo-Backend macht da keinen Unterschied, da der Abruf des jeweiligen Artikels über einen eigenen Prozess erfolgt.<br>
                    Abhilfe schaff hier u.U. die Umgehung  
                    über die Definition der eigenen IP in den Einstellungen des Wartungsmodus.</div>


                    <p class="faq text-danger" data-toggle="collapse" data-target="#f004"><span class="caret"></span> Meine Änderungen in der Arbeitsversion werden nicht berücksichtigt.</p>
                    <div id="f004" class="collapse">Da der Checkup nur die Liveversion einer Seite prüft, müssen soeben gemachte Änderungen für eine korrekte Prüfung erst Live geschalten werden.</div>



					<p>&nbsp;</p>
                    
                    <h3>Fragen, Wünsche, Probleme?</h3>
                    Du hast einen Fehler gefunden oder ein nettes Feature parat?<br>
				Lege ein Issue unter <a href="<?php echo $this->getProperty('supportpage'); ?>" target="_blank"><?php echo $this->getProperty('supportpage'); ?></a> an. 
                
                
                    
                    <h3>Credits</h3>
                    WDF-Kalkulator: <a href="https://github.com/rkemmere" target="_blank">Ronny Kemmereit</a>
                
                </div>
            </div>

	  </div>
	</div>
</section>