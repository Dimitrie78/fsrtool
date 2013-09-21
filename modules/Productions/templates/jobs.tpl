{include file="header.tpl"}
{include file="file:[Productions]menu.tpl"}

<script type="text/javascript" src="modules/Productions/inc/Productions.js"></script>

<div id="ajaxInProgress"></div>
  <div id="cacheInfo">
    <a class="cachedUntil timer">0</a>
  </div>
  
  <div id="main">
  
  <h1>{$curUser->corpName} Produktionsinformationen</h1>
  <div id="error"></div>
  
  <!--<div class="col" style="width:250px;">
    <b>Update 13.11.</b><br/>
	<ul>
	<li>Indikatoren f&uuml;r Schiffe und Blueprints hinzugef&uuml;gt</li>
	</ul> 
	<b>Update 26.09.</b><br/>
	<ul>
	<li>Cookie Problem gel&ouml;st; AHOHA sollte jetzt auch ohne Probleme reinkommen ;)</li>
	</ul>
    <b>Update 15.09.</b><br/>
	<ul>
	<li>Verbesserte Anzeige von Sortierten Spalten</li>
	<li>Einzelne Bugfixes und Optimierungen</li>
	</ul>
	coming soon:
	<ul>
	<li>tabelleninterne Suchfunktion</li>
	<li>benutzerdefinierte Standardsortierung</li>
	</ul>
  </div>-->
  
  <div class="col">
  <table id="chars" cellpadding="3" cellspacing="0">
    <thead>
	  <tr class="desc">
		<th colspan="3">produzierende Chars:</th>
	  </tr>
      <tr>
        <th>Name</th>
        <th>M</th>
        <th>R</th>
      </tr>
    </thead>
    <tbody>
      <tr class="template">
        <td><span class="char_name">name</span></td>
        <td align="right"><span class="char_m">M</span></td>
        <td align="right"><span class="char_r">R</span></td>
      </tr>
    </tbody>
  </table>

  <table id="activity" cellpadding="3" cellspacing="0">
    <thead>
	  <tr class="desc">
		<th colspan="4">Jobtypen:</th>
	  </tr>
      <tr>
        <th>Typ</th>
        <th>Anzahl</th>
        <th>Station</th>
        <th>POS</th>
      </tr>
    </thead>
    <tbody>
      <tr class="template">
        <td align="center"><span class="act_type">type</span></td>
        <td align="right"><span class="act_qty">qty</span></td>
        <td align="right"><span class="act_stn">0</span></td>
        <td align="right"><span class="act_pos">0</span></td>
      </tr>
	  <tr class="foot">
	    <th align="center">ges.:</th>
	    <th align="right"><span class="act_all">0</span></th>
	    <th align="right"><span class="act_all_stn">0</span></th>
	    <th align="right"><span class="act_all_pos">0</span></th>
	  </tr>
	</tbody>
  </table>
  </div>  
  
  <div class="col">
  <table id="manufacturing" cellpadding="3" cellspacing="0">
    <thead>
	  <tr class="desc">
		<th colspan="8">Capitalteile in Produktion/Bestand</th>
	  </tr>
      <tr>
        <th>Produkt</th>
        <th>in Bau</th>
        <th>Jobs</th>
      	<th>n&auml;chste fertig</th>
	    <th>n&auml;chster Batch</th>
	    <th>Bestand ges.</th>
		<th>@Stn</th>
		<th>@POS</th>
      </tr>
    </thead>
    <tbody>
      <tr class="template">
        <td><span class="man_product">product</span></td>
        <td align="right"><span class="man_qty">qty</span></td>
        <td align="right"><span class="man_jobs">jobs</span></td>
        <td align="right"><span title="nxt_rdy" class="man_nxt_rdy timer">nxt_rdy</span></td>
        <td align="right"><span class="man_nxt_batch">nxt_batch</span></td>
        <td align="right"><span class="man_stock">stock</span></td>
        <td align="right"><span class="man_stock_stn">stock @stn</span></td>
        <td align="right"><span class="man_stock_pos">stock @pos</span></td>
      </tr>
	</tbody>
  </table>
  </div>
  
  <div class="col clr">
  <table id="jobs" cellpadding="3" cellspacing="0">
    <thead>
	  <tr class="desc">
		<th colspan="12">Job Einzelauflistung:</th>
	  </tr>
      <tr>
        <th>Produkt</th>
		<th>Typ</th>
        <th>Anzahl</th>
        <th>System</th>
        <th>Standort</th>
        <th>Hangar</th>
        <th>Produzent</th>
        <th>ML</th>
        <th>PL</th>
        <th>Fertigstellung in</th>
        <th>Enddatum</th>
        <th>Startdatum</th>
      </tr>
    </thead>
    <tbody>
      <tr class="template">
        <td><span class="job_product">product</span></td>
        <td align="center"><span class="job_type">type</span></td>
        <td align="right"><span class="job_qty">qty</span></td>
        <td><span class="job_sys">sys</span></td>
        <td><span class="job_location">location</span></td>
        <td align="center"><span class="job_output_in">output in</span></td>
        <td><span class="job_installer">installer</span></td>
        <td align="right"><span class="job_ml">ml</span></td>
        <td align="right"><span class="job_pl">pl</span></td>
        <td align="right"><span title="ttc" class="job_ttc timer">ttc</span></td>
        <td align="right"><span class="job_endtime">endtime</span></td>
        <td align="right"><span title="installtime" class="job_installtime">installtime</span></td>
      </tr>
    </tbody>
  </table>  
  </div>
  
  <div class="clr" id="copy">&copy; 2010 by Guerilla</div>
  </div><!-- end #main -->
  <div id="topanchor">
	<a href="#" title="scroll to top" rel="nofollow">nach oben</a>
  </div>


{include file="footer.tpl"}