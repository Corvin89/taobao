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

class HSS_FrameBreaker extends HS_SiteModule
{
	var $wpautop     = false;
	var $clickable   = true;
	var $wptexturize = false;
	
	function name ()
	{
		return __ ('Frame Breaker', 'headspace');
	}
	
	function description ()
	{
		return __ ('Stops your site being loaded in a frame.', 'headspace');
	}
	
	function head ()
	{
		if ($_SERVER['HTTP_REFERER'] != get_bloginfo ('wpurl').'/wp-admin/themes.php')
		{
			?>
			<script type="text/javascript">
			//<![CDATA[
			  if (top.location != location)
			    top.location.href = document.location.href ;
			//]]>
			</script>
			<?php
		}
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>