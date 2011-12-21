<?php
	
class TermPager
{
	var $order_by = 'tag';
	var $url;
	var $current_page = 0;
	var $order_direction = 'ASC';
	
	function TermPager($get, $url, $default_order, $default_direction) {
		$this->url = $url;

		$this->order_by        = $default_order;
		$this->order_direction = $default_direction;
		
		if (isset ($get['orderby']))
			$this->order_by = $get['orderby'];
			
		if (isset ($get['order']))
			$this->order_direction = $get['order'];
	}
	
	function get($taxonomy) {
		return get_terms ($taxonomy, array ('get' => 'all', 'hide_empty' => false, 'orderby' => $this->order_by, 'order' => $this->order_direction, 'number' => '2,2'));
	}
	
	function sortable($text, $column) {
		$url = $this->url ($this->current_page, $column);
		
		if (isset ($this->order_tags[$column]))
			$column = $this->order_tags[$column];
			
		if ($column == $this->order_by) {
			if (defined ('WP_PLUGIN_URL'))
				$dir = WP_PLUGIN_URL.'/'.basename (dirname (dirname (__FILE__)));
			else
				$dir = get_bloginfo ('wpurl').'/wp-content/plugins/'.basename (dirname (dirname (__FILE__)));
				
			if (strpos ($url, 'ASC') !== false)
				$img = '<img align="bottom" src="'.$dir.'/images/up.gif" alt="dir" width="16" height="7"/>';
			else
				$img = '<img align="bottom" src="'.$dir.'/images/down.gif" alt="dir" width="16" height="7"/>';
		}
		
		return '<a href="'.$url.'">'.$text.'</a>'.$img;
	}
	
	function url($offset, $orderby = '') {
		// Position
		if (strpos ($this->url, 'curpage=') !== false)
			$url = preg_replace ('/curpage=\d*/', 'curpage='.$offset, $this->url);
		else
			$url = $this->url.'&amp;curpage='.$offset;
			
		// Order
		if ($orderby != '') {
			if (strpos ($url, 'orderby=') !== false)
				$url = preg_replace ('/orderby=\w*/', 'orderby='.$orderby, $url);
			else
				$url = $url.'&amp;orderby='.$orderby;
			
			if (!empty ($this->order_tags) && isset ($this->order_tags[$orderby]))
				$dir = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
			else if ($this->order_by == $orderby)
				$dir = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
			else
				$dir = $this->order_direction;
				
			if (strpos ($url, 'order=') !== false)
				$url = preg_replace ('/order=\w*/', 'order='.$dir, $url);
			else
				$url = $url.'&amp;order='.$dir;
		}
		
		return str_replace ('&go=go', '', $url);
	}
}
?>