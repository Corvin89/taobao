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

class HSS_SystemSettings extends HS_SiteModule
{
	var $memory_limit = 0;
	var $timeout = -1;
	var $errors = 'default';
	
	function name ()
	{
		return __ ('System Settings', 'headspace');
	}
	
	function description ()
	{
		return __ ('Configure PHP memory limits, time-outs, and error reporting', 'headspace');
	}
	
	function run ()
	{
		if ($this->errors == 'error')
			error_reporting (E_ERROR);
		else if ($this->errors == 'all')
			error_reporting (E_ALL);
		else if ($this->errors == 'none')
			error_reporting (0);
		
		if ($this->memory_limit > 0)
			ini_set ('memory_limit', $this->memory_limit.'M');
			
		if ($this->timeout != -1)
			set_time_limit ($this->timeout);
	}

	function load ($data)
	{
		if (isset ($data['memory_limit']))
			$this->memory_limit = $data['memory_limit'];
			
		if (isset ($data['timeout']))
			$this->timeout = $data['timeout'];

		if (isset ($data['errors']))
			$this->errors = $data['errors'];
	}
	
	function has_config () { return true; }
	
	function save_options ($data)
	{
		return array
		(
			'memory_limit' => intval ($data['memory_limit']),
			'timeout'      => intval ($data['timeout']),
			'errors'       => $data['errors']
		);
	}
	
	function edit ()
	{
		$timeouts = array
		(
			'-1'   => __ ('System default', 'headspace'),
			'30'   => __ ('30 seconds', 'headspace'),
			'60'   => __ ('60 seconds', 'headspace'),
			'600'  => __ ('10 minutes', 'headspace'),
			'3600' => __ ('1 hour', 'headspace'),
			'0'    => __ ('No timeout limit', 'headspace')
		);
		
		$limits = array
		(
			'0' => __ ('System default', 'headspace'), '16' => '16M', '32' => '32M', '64' => '64M', '128' => '128M'
		);
		
		$reports = array
		(
			'default' => __ ('System default', 'headspace'),
			'error'   => __ ('Show only errors', 'headspace'),
			'all'     => __ ('Show all errors &amp; warnings', 'headspace'),
			'none'    => __ ('Show no errors or warnings', 'headspace')
		);
	?>
	<tr>
		<th width="150"><?php _e ('Memory Limit', 'headspace'); ?>:</th>
		<td>
			<select name="memory_limit">
					<?php foreach ($limits as $key => $text) : ?>
						<option value="<?php echo $key ?>"<?php if ($this->memory_limit == $key) echo ' selected="selected"'; ?>><?php echo $text ?></option>
					<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('PHP Script Timeout', 'headspace'); ?>:</th>
		<td>
			<select name="timeout">
					<?php foreach ($timeouts as $key => $text) : ?>
						<option value="<?php echo $key ?>"<?php if ($this->timeout == $key) echo ' selected="selected"'; ?>><?php echo $text ?></option>
					<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th width="150"><?php _e ('PHP Error Reporting', 'headspace'); ?>:</th>
		<td>
			<select name="errors">
					<?php foreach ($reports as $key => $text) : ?>
						<option value="<?php echo $key ?>"<?php if ($this->errors == $key) echo ' selected="selected"'; ?>><?php echo $text ?></option>
					<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
	}
	
	function file ()
	{
		return basename (__FILE__);
	}
}

?>