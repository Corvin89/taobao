<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php $this->render_admin ('annoy'); ?>

	<?php screen_icon(); ?>
	
	<h2><?php echo HEADSPACE_META; ?></h2>

	<form method="get" action="<?php echo $this->url ($pager->url) ?>">
		<p class="search-box">
			<label for="post-search-input" class="hidden"><?php _e ('Search') ?>:</label>
			<input class="search-input" type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars ($_GET['search']) : ''?>"/>
			<input class="button-secondary" type="submit" name="go" value="<?php _e ('Search', 'drain-hole') ?>"/>
			
			<input type="hidden" name="page" value="headspace.php"/>
			<input type="hidden" name="curpage" value="<?php echo $pager->current_page () ?>"/>
		</p>

		<div id="pager" class="tablenav">
			<div class="alignleft actions">
				<?php _e ('Meta-data', 'headspace'); ?>: <select name="type">
					<?php foreach ($types AS $name => $type) : ?>
						<option value="<?php echo $type->id () ?>"<?php if ($name == $current->id ()) echo ' selected="selected"' ?>><?php echo $type->name () ?></option>
					<?php endforeach; ?>
				</select>

				<?php $pager->per_page ('drain-hole'); ?>

				<input type="submit" value="<?php _e('Filter'); ?>" class="button-secondary" />

				<br class="clear" />
			</div>
		
			<div class="tablenav-pages">
				<?php echo $pager->page_links (); ?>
			</div>
		</div>
	</form>
	
	<form action="" method="post" accept-charset="utf-8">
	<table class="widefat post fixed">
		<thead>
			<tr>
				<th style="width: 40px; text-align: center"><?php echo $pager->sortable ('id', 'ID') ?></th>
				<?php $current->show_header ($pager); ?>
			</tr>
		</thead>

		
		<tbody>
			<?php if (count ($posts) > 0) : ?>
			<?php foreach ($posts AS $pos => $post) : ?>
				<tr<?php if ($pos % 2 == 1) echo ' class="alt"' ?>>
					<td align="center"><a href="post.php?action=edit&amp;post=<?php echo $post->ID ?>"><?php echo $post->ID ?></a></td>
					<?php $current->show ($post, $pager); ?>
				</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	
	<?php wp_nonce_field ('headspace-mass_edit'); ?>
	
	<div style="clear: both"></div>
	<br/>
	<input class="button-primary" type="submit" name="save" value="<?php _e ('Save all data', 'headspace'); ?>" id="save"/>
	<br/><br/>
	
	</form>
	
	
	<script type="text/javascript">
	jQuery(document).ready( function() {
		jQuery( 'a.auto-desc' ).AutoDescription( { target: '#edit_' } );
		jQuery( 'a.tag' ).AutoTag();
		jQuery( 'a.title' ).AutoTitle();
	});
	</script>
</div>
