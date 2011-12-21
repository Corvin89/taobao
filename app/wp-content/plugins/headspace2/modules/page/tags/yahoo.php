<?php

/**
 * HeadSpace
 *
 * @package HeadSpace
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

class HS_TagYahoo
{
	var $url = 'http://api.search.yahoo.com/ContentAnalysisService/V1/termExtraction';
	
	function HS_TagYahoo ($api) {
		$this->api = $api;
	}
	
	function matches ($text) {
		if (function_exists ('curl_init')) {
			$ch = curl_init ();
		
			curl_setopt ($ch, CURLOPT_URL, $this->url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, array ('appid' => $this->api, 'output' => 'php', 'context' => $text));
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		
			$tags = array ();
			if (($output = curl_exec ($ch))) {
				$output = unserialize ($output);
				if (isset ($output['ResultSet']['Result']))
					$tags = $output['ResultSet']['Result'];
			}
		
			curl_close ($ch);
			return $tags;
		}
		else
			return array ('You do not have CURL installed');
	}
}

?>