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

class HSS_DisableVisual extends HS_SiteModule
{
	function name ()
	{
		return __ ('Disable Visual Editing', 'headspace');
	}
	
	function description ()
	{
		return __ ('Disable visual editing', 'headspace');
	}
	
	function can_edit ()
	{
		return false;
	}
	
	function run ()
	{
		add_filter ('user_can_richedit', array (&$this, 'can_edit'));
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>