<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
	<?php screen_icon(); ?>
	
	<h2><?php _e ('HeadSpace | Support', 'headspace'); ?></h2>
	<?php $this->submenu (true); ?>
	<p><?php _e ('HeadSpace has required a great deal of time and effort to develop.  If it\'s been useful to you then you can support this development by <strong>making a small donation of $12</strong>.  This will act as an incentive for me to carry on developing it, providing countless hours of support, and including any enhancements that are suggested.', 'headspace'); ?></p>
	
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick"/>
		<input type="hidden" name="business" value="admin@urbangiraffe.com"/>
		<input type="hidden" name="item_name" value="HeadSpace"/>
		<input type="hidden" name="amount" value="12.00"/>
		<input type="hidden" name="buyer_credit_promo_code" value=""/>
		<input type="hidden" name="buyer_credit_product_category" value=""/>
		<input type="hidden" name="buyer_credit_shipping_method" value=""/>
		<input type="hidden" name="buyer_credit_user_address_change" value=""/>
		<input type="hidden" name="no_shipping" value="1"/>
		<input type="hidden" name="return" value="http://urbangiraffe.com/plugins/headspace2/"/>
		<input type="hidden" name="no_note" value="1"/>
		<input type="hidden" name="currency_code" value="USD"/>
		<input type="hidden" name="tax" value="0"/>
		<input type="hidden" name="lc" value="US"/>
		<input type="hidden" name="bn" value="PP-DonationsBF"/>
		<input type="image" style="border: none; margin: 0 auto; text-align: center; display: block" src="<?php echo $this->url () ?>/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!"/>
	</form>
	
	<p><?php _e ('Alternatively, if you are multi-lingual, do consider translating this into another language.  All the necessary localisation files are included and I\'ve written a <a href="http://urbangiraffe.com/articles/translating-wordpress-themes-and-plugins/">full guide to the translation process</a>.', 'headspace'); ?></p>
	
	<p><?php _e ('The support message can be disabled at any time from the options page', 'headspace'); ?>
</div>