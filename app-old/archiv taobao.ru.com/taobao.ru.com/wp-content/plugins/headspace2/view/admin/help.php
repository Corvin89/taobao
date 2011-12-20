<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap" id="help">
	<h2><?php _e ('Special Tags', 'headspace') ?></h2>
	<p><?php _e ('These tags can be included and will be replaced by HeadSpace when a page is displayed.', 'headspace'); ?></p>
	
	<?php
		$pos = 0;
		$help = array
		(
			'date'                 => __( 'Replaced with the date of the post/page', 'headspace'),
			'title'                => __( 'Replaced with the title of the post/page', 'headspace'),
			'sitename'             => __( 'The site\'s name', 'headspace'),
			'excerpt'              => __( 'Replaced with the post/page excerpt (or auto-generated if it does not exist)', 'headspace'),
			'excerpt_only'         => __( 'Replaced with the post/page excerpt (without auto-generation)', 'headspace'),
			'tag'                  => __( 'Replaced with the current tag/tags', 'headspace'),
			'category'             => __( 'Replaced with the post categories (comma separated)', 'headspace'),
			'category_description' => __( 'Replaced with the category description', 'headspace'),
			'tag_description'      => __( 'Replaced with the tag description', 'headspace'),
			'term_description'     => __( 'Replaced with the term description', 'headspace'),
			'term_title'           => __( 'Replaced with the term name', 'headspace'),
			'modified'             => __( 'Replaced with the post/page modified time', 'headspace'),		
			'id'                   => __( 'Replaced with the post/page ID', 'headspace'),
			'name'                 => __( 'Replaced with the post/page author\'s \'nicename\'', 'headspace'),
			'userid'               => __( 'Replaced with the post/page author\'s userid', 'headspace'),
			'searchphrase'         => __( 'Replaced with the current search phrase', 'headspace'),
			'currenttime'          => __( 'Replaced with the current time', 'headspace'),
			'currentdate'          => __( 'Replaced with the current date', 'headspace'),
			'currentmonth'         => __( 'Replaced with the current month', 'headspace'),
			'currentyear'          => __( 'Replaced with the current year', 'headspace'),
			'page'                 => __( 'Replaced with the current page number (i.e. page 2 of 4)', 'headspace'),
			'pagetotal'            => __( 'Replaced with the current page total', 'headspace'),
			'pagenumber'           => __( 'Replaced with the current page number', 'headspace'),
			'caption'              => __( 'Attachment caption', 'headspace')
		);
	?>
	
	<table class="help">
		<?php foreach ($help AS $tag => $text) : ?>
		<tr<?php if ($pos++ % 2 == 1) echo ' class="alt"' ?>>
			<th>%%<?php echo $tag; ?>%%</th>
			<td><?php echo $text; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>