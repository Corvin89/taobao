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

class HSS_GoogleAdWrap extends HS_SiteModule
{
	function name ()
	{
		return __ ('Google Section Targeting', 'headspace');
	}
	
	function description ()
	{
		return __ ('Wraps all post and page content inside a Google targeted section', 'headspace');
	}
	
	function google_ad_wrap ($text)
	{
		if ( !is_feed() )
			return "<!-- google_ad_section_start -->".$text."<!-- google_ad_section_end -->";
		return $text;
	}

	function run ()
	{
		add_filter ('the_content', array (&$this, 'google_ad_wrap'));
		add_filter ('the_excerpt', array (&$this, 'google_ad_wrap'));
		add_filter ('the_excerpt_reloaded', array (&$this, 'google_ad_wrap'));
		add_filter ('comment_text', array (&$this, 'google_ad_wrap'));
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>
