<?php $catalogLink = '<table border="1"> 
        <tr>
          <td bordercolor="#CCCCCC"> 
            <table border="0"><tr><tr>
	<td><a href="http://www.cycle-source.co.uk"><img src="http://www.cycle-source.co.uk/csource.gif" border="0"></a>
	</td></tr><td><form action="http://www.cycle-source.co.uk/cgi-bin/search.cgi" method="GET">
        <div class="margin">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr><td>Search: <input type="TEXT" name="query" size="30"> <input type="Submit" value="Search"></td></tr>
                <tr><td>Number of Results: <SELECT name="mh"><OPTION>10<OPTION SELECTED>25<OPTION>50<OPTION>100</SELECT></td></tr>
                <tr><td>As Keywords: <input type="RADIO" name="type" value="keyword" CHECKED> As Phrase: <input type="RADIO" name="type" value="phrase"></td></tr>
                <tr><td>AND connector: <input type="RADIO" name="bool" value="and" CHECKED> OR connector: <input type="RADIO" name="bool" value="or"></td></tr>
            </table>
        </div>
    </form></td></tr></table></td></tr></table>'; include '../view.php';