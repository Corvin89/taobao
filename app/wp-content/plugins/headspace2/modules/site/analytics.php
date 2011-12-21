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

class HSS_Analytics extends HS_SiteModule {
	var $tracking    = '';
	var $role        = 'everyone';
	var $outbound    = '';
	var $version     = 'ga';
	var $virtual     = '';
	var $domain      = '';
	var $raw         = '';
	var $login       = false;
	
	var $trackable   = null;
	
	function name()	{
		return __( 'Google Analytics', 'headspace' );
	}
	
	function description() {
		return __( 'Adds Google Analytic tracking code to all pages (through <code>wp_footer</code>)', 'headspace' );
	}
	
	function run()	{
		add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
		
		if ( $this->login )
			add_action( 'login_head', array( &$this, 'wp_footer' ) );
	}
	
	function is_trackable()	{
		if ( $this->trackable !== null )
			return $this->trackable;
			
		if ( is_user_logged_in () && $this->role != 'everyone' )	{
			$user = wp_get_current_user ();
			
			global $wp_roles;
			$caps = $wp_roles->get_role( $this->role );
			
			if ( $caps ) {
				// Calculate the highest level of the user and the role
				$role_level = $user_level = 0;
				for ( $x = 10; $x >= 0; $x-- ) {
					if ( isset( $caps->capabilities['level_'.$x] ) )
						break;
				}
			
				$role_level = $x;

				for ( $x = 10; $x >= 0; $x-- ) {
					if ( isset( $user->allcaps['level_'.$x] ) )
						break;
				}
			
				$user_level = $x;
			
				// Quit if the user is greater level than the role
				if ( $user_level > $role_level ) {
					$this->is_trackable = false;
					return false;
				}
			}
		}
		
		$this->is_trackable = true;
		return $this->is_trackable;
	}
	
	function wp_footer() {
		if ( $this->tracking && $this->is_trackable() && $this->version == 'urchin' )
			$this->track_urchin();
	}
	
	function head() {
		if ( $this->tracking && $this->is_trackable() && $this->version == 'ga' )
			$this->track_ga();
	}
	
	function track_urchin() {
		$virtual = '';
		if ( $this->virtual )
			$virtual = '"'.$this->virtual.'"';
		?>
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "<?php echo $this->tracking ?>";
<?php if ($this->domain) echo '_udn = "'.$this->domain.'";'."\r\n"; ?>
<?php if ($this->raw) echo $this->raw."\r\n"; ?>
urchinTracker(<?php echo $virtual ?>);
</script>
		<?php
		
		// Output code for outbound tracking
		if ( $this->outbound ) {
			?>
<script type="text/javascript">
	// Outbound link tracking
	//<![CDATA[
	for (var i = 0; i < document.links.length; i++)
	{
		if (document.links[i].href.indexOf( '<?php bloginfo( 'home' ) ?>' ) == -1)
			document.links[i].onclick = function ()
				{
					var domain = this.href.match( /:\/\/(www\.)?([^\/:]+)/ );
	    		domain = domain[2] ? domain[2] : '';
	    		urchinTracker( '/<?php echo $this->outbound ?>/' + domain.replace (/\./g, '_' ));
				}
	}
	//]]>
</script>
			<?php
		}
	}
	
	function track_ga() {
?>
		
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo $this->tracking ?>']);
  _gaq.push(['_trackPageview']);

<?php if ( $this->domain ) : ?>
	_gaq.push(['_setDomainName'], '<?php echo esc_js( $this->domain ); ?>' );
<?php endif; ?>

<?php if ( $this->virtual ) : ?>
	_gaq.push(['_trackPageview'], '<?php echo esc_js( $virtual ); ?>' );
<?php endif; ?>

<?php if ( $this->outbound ) : ?>
	for (var i = 0; i < document.links.length; i++)	{
		if (document.links[i].href.indexOf( '<?php bloginfo( 'home' ) ?>' ) == -1)
			document.links[i].onclick = function()	{
				var domain = this.href.match( /:\/\/(www\.)?([^\/:]+)/ );
    		domain = domain[2] ? domain[2] : '';
				_gaq.push(['_trackPageview'], '/<?php echo $this->outbound ?>/' + domain.replace (/\./g, '_' ) );
			}
	}
<?php endif; ?>

  (function() {
    var ga = document.createElement('script' ); ga.type = 'text/javascript'; ga.async = true;
    ga.src =( 'https:' == document.location.protocol ? 'https://ssl' : 'http://www' ) + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script' )[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>	
<?php
	}
	
	function load( $data ) {
		$load = array( 'tracking', 'role', 'outbound', 'raw', 'virtual', 'domain', 'version', 'login' );
		
		foreach ( $load AS $key ) {
			if ( isset( $data[$key] ) )
				$this->$key = $data[$key];
		}
	}
	
	function has_config() {
		return true;
	}
	
	function save_options( $data )	{
		$code = trim( $data['tracking'] );
		
		if ( strpos( $code, 'text/javascript' ) !== false ) {
			if ( strpos( $code, '_uacct' ) !== false )
				preg_match( '@_uacct = "(.*?)";@', $code, $matches );
			else
				preg_match( '@_getTracker\("(.*?)"\);@', $code, $matches );
				
			if ( count( $matches ) > 0 )
				$code = $matches[1];
		}

		return array (
			'tracking' => $code,
			'role'     => $data['role'],
			'outbound' => trim ($data['outbound']),
			'raw'      => trim ($data['raw']),
			'virtual'  => $data['virtual'],
			'domain'   => $data['domain'],
			'version'  => $data['version'],
			'login'    => isset( $data['login'] ) ? true : false
		);
	}
	
	function edit() {
	?>
	<tr>
		<th width="150"><?php _e( 'Account ID', 'headspace' ); ?>:</th>
		<td>
			<textarea rows="5" cols="40" name="tracking"><?php esc_attr_e( $this->tracking); ?></textarea><br/>
			<span class="sub"><?php _e( 'Enter the full Google JavaScript tracking code, or just the <code>_uacct</code> number.', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Version', 'headspace' ); ?>:</th>
		<td>
			<select name="version">
				<option <?php if ($this->version == 'ga' ) echo 'selected="selected" '; ?>value="ga"><?php _e( 'Async, Google Analytics (ga.js)', 'headspace' ); ?></option>
				<option <?php if ($this->version == 'urchin' ) echo 'selected="selected" '; ?>value="urchin"><?php _e( 'Urchin (urchin.js)', 'headspace' ); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Who to track', 'headspace' ); ?>:</th>
		<td>
			<select name="role">
				<option value="everyone"><?php _e( 'Everyone', 'headspace' ); ?></option>
					<?php global $wp_roles; foreach ($wp_roles->role_names as $key => $rolename) : ?>
						<option value="<?php echo $key ?>"<?php if ($this->role == $key) echo ' selected="selected"'; ?>><?php echo $rolename ?></option>
					<?php endforeach; ?>
				</select>
			</select>
			
			<span class="sub"><?php _e( 'Users of the specified role or less will be tracked', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Track outbound links', 'headspace' ); ?>:</th>
		<td>
			<input size="30" type="text" name="outbound" value="<?php esc_attr_e( $this->outbound) ?>"/>
			<span class="sub"><?php _e( 'Enter the URL you want outbound links tracked to', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Virtual Pages', 'headspace' ); ?>:</th>
		<td>
			<input size="30" type="text" name="virtual" value="<?php esc_attr_e( $this->virtual) ?>"/>
			<span class="sub"><?php _e( 'Change what appears in reports', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Domain Name', 'headspace' ); ?>:</th>
		<td>
			<input size="30" type="text" name="domain" value="<?php esc_attr_e( $this->domain) ?>"/>
			<span class="sub"><?php _e( 'Set to a root domain when tracking across sub-domains', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Raw Code', 'headspace' ); ?>:</th>
		<td>
			<textarea rows="5" cols="40" name="raw"><?php esc_attr_e( $this->raw); ?></textarea><br/>
			<span class="sub"><?php _e( 'Enter any additional Google Analytics code', 'headspace' ); ?></span>
		</td>
	</tr>
	<tr>
		<th><?php _e( 'Include on login page', 'headspace' ); ?>:</th>
		<td>
			<input type="checkbox" name="login"<?php if ( $this->login ) echo ' checked="checked"' ?>/>
			<span class="sub"><?php _e( 'Include Google Analytics on the WordPress login page', 'headspace' )?></span>
		</td>
	</tr>
	
	<?php
	}
	
	function file() {
		return basename( __FILE__ );
	}
}
