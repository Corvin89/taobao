<?php
defined( '_KIH_WEBDIR_' ) or die( '' );

if (defined( '_KIHLIB_WP_LINKS_')) return;

define( _KIHLIB_WP_LINKS_, true );

/**
 * @package Kih Links
 *
 * @abstract Class library for creating a web directory in wordpress
 * @version 2.6.5
 * @author Gerry Ilagan
 * @copyright 2002-2010 by Gerry Ilagan
 */
class _KIH_WP_LINKS {

	var $options;

	// valid last request
	var $valid_request;

	var $requested_linkcat;

	var $current = array();

	var $pagenum = 0;

	// filter unwanted characters for validation
	var $filter = array(' ','&','*','%','/','\\',"'",'"',';');

	function _KIH_WP_LINKS( $options ) {

		$this->options = $options;

		$this->valid_request = "false";

		$this->current['slug'] = "";
		$this->current['name'] = "";
		$this->current['description'] = "";

		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'init', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'generate_rewrite_rules', array( $this, 'rewrite_rules' ) );
		add_shortcode('wplinks', array($this, 'directory') );

		add_filter( 'wp_title', array($this, 'filter_title'), 10, 3 );

		//if (is_callable(array('kih_theme','description')) ) {
			add_filter( 'kih_wp_head_desc', array($this, 'filter_desc'), 10, 1 );
		//} else {

		//}
	}

	/**
	 * Get a list of link categories
	 *
	 * @return object - list of categories
	 */
	function get_categories() {
		global $wpdb;

		$categories = $wpdb->get_results("
						SELECT *
						FROM $wpdb->terms t
						LEFT JOIN $wpdb->term_taxonomy tt
						ON t.term_id = tt.term_id
						WHERE tt.taxonomy = 'link_category'
						ORDER BY t.name
					");

		return $categories;
	}

	/**
	 * Return a list of bookmarks under a given link category
	 *
	 * @param $cat - the link category slug
	 *
	 * @return object - list of bookmarks
	 */
	function get_bookmarks( $cat=null ) {
		global $wpdb;

		// remove the characters, they are not needed anyway
		$cat = str_replace( $this->filter, '', $cat );

		// just in case i forgot something
		$cat = addslashes( $cat );

		$which_cat = ( $cat ? " AND t.slug = '$cat' " : "" );

		$bookmarks = $wpdb->get_results("
						SELECT *
						FROM $wpdb->term_relationships r
						LEFT JOIN $wpdb->term_taxonomy tt
						ON r.term_taxonomy_id = tt.term_taxonomy_id
						LEFT JOIN $wpdb->terms t
						ON t.term_id = tt.term_id
						LEFT JOIN $wpdb->links l
						ON l.link_id = r.object_id
						WHERE tt.taxonomy = 'link_category'
						$which_cat
						ORDER BY l.link_name
					");

		return $bookmarks;
	}

	/**
	 * Return list of categories with formatting
	 *
	 * @param $beforelist - something to print before the start of the list
	 * @param $afterlist  - something to print after the start of the list
	 * @param $beforeitem - something to print before a category item
	 * @param $afteritem  - something to print after a category item
	 * @param $display    - if true echo the list, return a string of the contents if false
	 * @return string - what should have been displayed
	 */
	function list_categories( $beforelist='',$afterlist='',
								$beforeitem='',$afteritem='',$display=false	) {

		if (empty($beforelist))	$beforelist = '<div class="section-bar"><h2 class="section-title">'.
												__('Categories').
												'</h2></div>'.
												'<div class="link-categories">';

		if (empty($afterlist)) $afterlist = '</div><p class="nl"></p>';


		// Generate a list of categories that will be displayed on the web browser

		$out = $beforelist;

		foreach ( $this->get_categories() as $linkcat ) {

			$out .= $beforeitem .

				'<div class="link-category-box'.
				( $linkcat->slug == $this->requested_linkcat ? ' link-category-box-active' : '') .
				'"><div class="link-category"><h3 class="category-name">'.

				// make the appropriate link if permalinks is set
				'<a href="' . $this->create_permalink($linkcat) . '">' .
				$linkcat->name . '</a>' .

				'</h3><p class="category-desc">' .
				( empty($linkcat->description) ?
				sprintf( __('A list of web sites / blogs under %s'), $linkcat->name) :
				$linkcat->description ) .
				'</p><p class="item-count">' . ( $linkcat->count == 0 ? __('no site') :
				($linkcat->count > 1 ? $linkcat->count. __(' sites') : __('1 site') ) ) .
				'</p></div></div>' . $afteritem;
		}

		$out .= $afterlist;

		apply_filters( 'kih_webdir_categories', $out );

		if ($display) echo $out;
		else return $out;

	}

	/**
	 * Return list of bookmarks under a category with formatting
	 *
	 * @param $linkcat    - the selected category slug
	 * @param $beforelist - something to print before the start of the list
	 * @param $afterlist  - something to print after the start of the list
	 * @param $beforeitem - something to print before an item
	 * @param $afteritem  - something to print after an item
	 * @param $display    - if true echo the list, return a string of the contents if false
	 * @return string - what should have been displayed
	 */
	function list_bookmarks( $linkcat=array(), $beforelist='', $afterlist='',
								$beforeitem='', $afteritem='', $display=false) {

		$bookmark = array();

		if ( empty($linkcat) ) return '';

		if ( empty($linkcat['slug']) ) return '';

		if ( empty($beforelist) ) {
			$sectiontitle = ( $this->options['before_cattitle'] ?
								sprintf( __('Websites / Blogs under %s'), $linkcat['name'] )
								: $linkcat['name'] );

			$submitlink = $this->submit_link();

			$beforelist = '<div class="section-bar"><h2 class="section-title">'.
							$sectiontitle .
							'</h2>'.
							$submitlink .
							'</div>'.
							'<div class="link-bookmarks">';
		}

		if (empty($afterlist))
			$afterlist = '</div> <!-- /.link-bookmarks --><p class="nl"></p>';

		$bookmarks = $this->get_bookmarks( $linkcat['slug'] );

		$bookmarkcount = count($bookmarks);

		if ( $bookmarkcount ) {

			$out = $beforelist;

			$pagenav = "";
			$linksperpage = intval( $this->options['linksperpage'] );
			$start = 0;
			$end = $bookmarkcount;

			$pagenum = intval(trim($_GET['page']));

			if ( $linksperpage ) {

				$pagesofbookmarks = intval($bookmarkcount / $linksperpage);
				$pagesofbookmarks += ($bookmarkcount % $linksperpage ? 1 : 0);

				if ( $pagesofbookmarks > 1 ) {

					if ( $pagenum > $pagesofbookmarks ) {
						$pagenum = $pagesofbookmarks;
					} elseif ( $pagenum < 1 ) {
						$pagenum = 1;
					}

					$start = intval(($pagenum - 1) * $linksperpage);

					$end = $start + $linksperpage;

					if ( $end > $bookmarkcount ) $end = $bookmarkcount;

					$pagelinks = '';
					for ( $a=1; $a<=$pagesofbookmarks; $a++ ) {
						if ( $a == $pagenum )
							$pagelinks .= "<span class=\"currentpage\">$a</span>";
						else
							$pagelinks .= "<a class=\"pagelink\" href=\"?page=$a\">$a</a>";
					}

					$pagenav .= "<div class=\"bookmarksnav\">$pagelinks " .
								"<span class=\"navinfo\">Page ".
								"<span class=\"pagenum\">$pagenum</span> of ".
								"<span class=\"totalpages\">$pagesofbookmarks</span></span>".
								"</div><!--  Start: $start End: $end -->";

				} // end of if ( $pagesofbookmarks > 1 )

			} // end of if ( $linksperpage )

			// start generating the list of bookmarks
			for ( $i = $start; $i < $end; $i++) {

				$bookmark = $bookmarks[$i];

				$homebutton =	( file_exists(get_template_directory() . '/images/btn-links-home.png') ?
								 '<img src="' . get_bloginfo('template_url') .
								 '/images/btn-links-home.png" alt="H" title="' .
								 __('Home Page') . '" />' :
								 '<span class="button">&nbsp;Home&nbsp;</span>'
								);

				$rssbutton =	( file_exists(get_template_directory() . '/images/btn-links-rss-url.png') ?
								 '<img src="' . get_bloginfo('template_url') .
								 '/images/btn-links-rss-url.png" alt="R" title="' .
								 __('RSS feed') . '" />' :
								 '<span class="button">&nbsp;RSS&nbsp;</span>'
								);

				$out .= $beforeitem .
						'<div class="link-bookmark-box"><div class="link-bookmark">'.
						'<h3 class="bookmark-name"><a href="'.
						$bookmark->link_url . '">' .
						$bookmark->link_name . '</a></h3>' .
						'<p class="bookmark-desc">' .
						$bookmark->link_description . '</p>' .
						'<p class="bookmark-bar"><a title="' .
								 __('Website link') . '" href="' .
						$bookmark->link_url . '">'.$homebutton.'</a>'.
						( $bookmark->link_rss  ? '<a title="' .
								 __('Website RSS feed') . '" href="'.
							$bookmark->link_rss . '">'.$rssbutton.'</a>' : '' ) .
						'</p></div></div>'.
						$afteritem;
			}

			$out .= $pagenav;

			$out .= $afterlist;

		} else {

			$out = $beforelist;

			$out .= '<p class="none-found" style="margin-left:10px;">'.
					__('No sites under this category.') . '</p>';

			$out .= $afterlist;

		}

		apply_filters( 'kih_webdir_bookmarks', $out );

		if ($display) echo $out;
		else return $out;

	}

	/**
	 * Set class variables to the requested values
	 *
	 * @return void
	 */
	function get_request() {

		global $wp_query, $wp_rewrite;

		$this->requested_linkcat = '';

		// Retrieve the requested category to be displayed and sanitize it

		$requested = false;

		if ($wp_rewrite->using_permalinks()) {

			$requested = trim($wp_query->query_vars['linkcat']);

		} else {

			$requested = trim($_GET['linkcat']);

		}

		// assume request is invalid until checked below
		$this->valid_request = false;

		if ( empty($requested) || !$requested ) {
			$this->valid_request = true;
			// an empty slug means this is the main web directory page
			$this->current['slug'] = '';
			$this->current['name'] = $this->options['pagetitle'];
			$this->current['description'] = $this->options['metadesc'];

			return;
		}

		// the requested linkcat isn't empty so filter unwanted
		$requested = str_replace( $this->filter, '', strip_tags($requested) );

		// if requested linkcat is not empty initially assume request to be invalid
		if ( empty($requested) )
			$this->valid_request = true;
		else
			$this->valid_request = false;

		// checking for a valid requested linkcat
		foreach ( $this->get_categories() as $linkcat ) {
			if ($requested == $linkcat->slug) {
				// found a match so set valid request to true
				$this->valid_request = true;
				$this->current['slug'] = $linkcat->slug;
				$this->current['name'] = $linkcat->name;
				$this->current['description'] = $linkcat->description;

				$this->requested_linkcat = $this->current['slug'];
			}
		}

		$this->pagenum = intval(trim($_GET['page']));

		return;
	}

	/**
	 * Display the directory when the shortcode [wplinks] is used
	 *
	 * @param $attribs - shortcode attributes
	 * @param $content - optional content enclosed between the starting and ending shortcode
	 * @return $string - the directory to be displayed
	 */
	function directory( $attribs, $content=null ) {

		$this->get_request();

		$out = '';

		$the_intro = trim( $content );

		if ( !empty($the_intro) ) {

			$out ='<div class="intro-box">' . "\n";

			$out .= $the_intro;

			$out .= '</div> <!-- /.intro-box -->' . "\n";
		}

		if ( empty($this->options['pageslug']) || !$this->options['pageslug'] ||
			$this->options['pageslug'] == '' )
		{

			$out .= '<div class="error-msg">'.
					'<strong>'.__('Warning').'</strong>:<br/>'.
					__('The web directory is not configured properly. ' .
						'Please inform the site owner about it.') .
					'</div>';

			$out .= "\n";

			return $out;
		}

		$out .= '<div id="webdirectory">';

		$out .= "\n";

		$out .= $this->list_categories();

		$out .= "\n";

		if ( $this->valid_request ) {

			$out .= $this->list_bookmarks( $this->current );

			$out .= "\n";

		} else {

			$out = '<div class="error-msg"><p>'.
					'<strong>'.__('Problem(s) detected').'</strong><br/>'.
					__('The bookmark category does not exist.').'</p></div>' . $out;

			$out .= "\n";

		}

		$out .= '</div> <!-- /#webdirectory -->';

		$out .= "\n";

		return $out;
	}

	/**
	 * Create a permalink for the link category if the permalink
	 * system is being used
	 *
	 * @param $linkcat
	 * @return $string - the permalink
	 */
	function create_permalink( $linkcat ) {
		global $wp_rewrite;

		// make the appropriate link if permalinks is set

		if ( $wp_rewrite->using_permalinks() ) {
			return get_bloginfo('url').'/'. $this->options['pageslug'] . '/' . $linkcat->slug;
		}

		return '?pagename='.$this->options['pageslug'].'&linkcat='. $linkcat->slug;

	}

	/**
	 * Function to set the correct <title> value for SEO purposes
	 *
	 * @param string $title
	 * @param string $sep
	 * @param string $seplocation
	 * @return string
	 */
	function filter_title( $title, $sep, $seplocation ) {

		$this->get_request();

		if ( is_page($this->options['pagetitle']) ) {
			if ( empty($this->current['slug']) ) {
				return $this->options['pagetitle'] .
				(function_exists('aioseop_get_version') ? '' : " | " . get_bloginfo('name') )
				 ;
			} else {
				$page = '';
				$pagenum = intval($this->pagenum);

				if ( $pagenum > 1 ) $page = " &raquo; Page " . $pagenum;

				return $this->current['name'] . "$page | " .  $this->options['pagetitle'] .
						(function_exists('aioseop_get_version') ? '' : " | " . get_bloginfo('name') )
						;
			}
		}
		return $title;
	}

	/**
	 * Function to set the correct meta description for SEO
	 * This depends on the correct setting for the description
	 * field of the link category
	 *
	 * @param string $desc
	 * @return sting
	 */
	function filter_desc( $desc ) {

		$this->get_request();

		if ( is_page($this->options['pagetitle']) ) {
			if ( empty($this->current['description']) ) {
				return sprintf( __('A list of web sites / blogs under %s'), $this->current['name']);
			} else {
				return $this->current['description'];
			}
		}
		return $desc;
	}

	/**
	 * If the appropriate breadcrumb navigation is present, alter it
	 * to reflect the proper navigation path for the web directory
	 *
	 * @param $links - an array of the current breadcrumb values
	 * @return array - if needed, the modified breadcrumb
	 */
	function filter_breadcrumb( $links ) {

		$this->get_request();

		if ( is_page($this->options['pagetitle']) ) {
			if ( empty($this->current['slug']) ) {
				return $links;
			} else {
				if ( is_array($links) ) {

					$links[ count($links)-1 ]['cur'] = false;

					$tmp = array();
					$tmp['title'] = $this->current['name'];
					$tmp['url']   = $this->create_permalink($this->current);
					$tmp['cur']   = true;
					$links[] = $tmp;

					return $links;
				}
			}
		}
		return $links;
	}

	/**
	 * Add the linkcat query variable to WordPress to be able
	 * to use permalinks
	 *
	 * @param $qv - existing list of query variables
	 * @return array - modified list containing the linkcat variable
	 */
	function query_vars($qv) {

		$qv[] = 'linkcat';

		return $qv;
	}

	/**
	 * Flush the rewrite rules to be able to start using our
	 * permalinks
	 *
	 * @return void
	 */
	function flush_rewrite_rules()
	{
		global $wp_rewrite;

		$wp_rewrite->flush_rules();
	}

	/**
	 * Add the needed rewrite rules for the web directory
	 * permalink structure
	 *
	 * @param $wp_rewrite - existing rewrite rules
	 */
	function rewrite_rules($wp_rewrite) {
		$links_rules = array(
	     	$this->options['pageslug'].'/(.+?)/?$' => 'index.php?pagename='.
	     							$this->options['pageslug'].
	     							'&linkcat=' . $wp_rewrite->preg_index(1)
	       );

		$wp_rewrite->rules = $links_rules + $wp_rewrite->rules;
	}

	/**
	 * Add a "submit link" if possible (i.e. contact form 7 plugin and required form
	 * exists in the WordPress blog this plugin is installed in
	 */
	function submit_link( $label='', $url='' ) {
		$label = empty($label)?__('Submit URL'):$label;

		if ( empty($url) ) {
			$url = $this->options['submiturl'];
			if ( empty($url) ) $url = '/submiturl';
		}

		$submit = '';
		if ( function_exists( 'wpcf7_add_shortcode' ) ) {
			$submit = '<span class="submitbutton"><a class="submiturl" href="'.$url.'">'.
						$label.'</a></span>';
		}
		return apply_filters( 'kih_webdir_submiturl', $submit );
	}
}

/**
 *
 * @abstract A Contact Form module for [linkcat] and [linkcat*]
 * @version 2.6.5
 * @author Gerry Ilagan
 * @copyright 2002-2010 by Gerry Ilagan
 *
 * 2.6.5 - fixed a bug in referer detection
 */
class _KIH_WPCF7_LINKCAT {

	var $links;

	function _KIH_WPCF7_LINKCAT( $linksobj ) {

		$this->links = $linksobj;

		if ( function_exists( 'wpcf7_add_shortcode' ) ) {
			wpcf7_add_shortcode( 'linkcat', array($this,'shortcode_handler'), true );
			wpcf7_add_shortcode( 'linkcat*', array($this,'shortcode_handler'), true );

			add_filter( 'wpcf7_validate_linkcat', array($this,'validation_filter'), 10, 2 );
			add_filter( 'wpcf7_validate_linkcat*', array($this,'validation_filter'), 10, 2 );
		} else {
			return null;
		}
	}
	
	/* Retrieve needed values for the select form */
	function get_values( $valueonly=true ) {
		
		$wplinks = $this->links;

		$linkcategories = $wplinks->get_categories();

		if ( !$valueonly ) return $linkcategories;
		
		$values = array();
		
		foreach( $linkcategories as $category ) {
			$values[] = $category->slug;
		}
		
		return $values;
	}

	/* Shortcode handler */
	function shortcode_handler( $tag ) {
		global $wpcf7_contact_form;

		if ( ! is_array( $tag ) )
			return '';

		$type = $tag['type'];
		$name = $tag['name'];
		$options = (array) $tag['options'];

		if ( empty( $name ) )
			return '';

		$wplinks = $this->links;
			
		$atts = '';
		$id_att = '';
		$class_att = '';

		$defaults = array();

		if ( 'linkcat*' == $type )
			$class_att .= ' wpcf7-validates-as-required';

		foreach ( $options as $option ) {
			if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
				$id_att = $matches[1];

			} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
				$class_att .= ' ' . $matches[1];

			} elseif ( preg_match( '/^default:([0-9a-zA-Z_]+)$/', $option, $matches ) ) {
				$defaults = explode( '_', $matches[1] );
			}
		}

		if ( $id_att )
			$atts .= ' id="' . trim( $id_att ) . '"';

		if ( $class_att )
			$atts .= ' class="' . trim( $class_att ) . '"';

		$include_blank = (bool) preg_grep( '%^include_blank$%', $options );

		$linkcategories = $this->get_values( false );

		$html = '';

		$posted = is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) && $wpcf7_contact_form->is_posted();

		foreach( $linkcategories as $category ) {
			$selected = false;

			if ( ! $empty_select && in_array( $category->slug, (array) $defaults ) )
				$selected = true;

			$catlink = $wplinks->create_permalink( $category );

			if ( htmlentities($_SERVER['HTTP_REFERER']) == $catlink )
				$selected = true;
			else
				$selected = false;

			$selected = $selected ? ' selected="selected"' : '';

			if ( isset( $category->name ) && ! empty( $category->name ) )
				$label = $category->name;
			else
				$label = $category->slug;

			$html .= '<option value="' . esc_html($category->slug) . '"' . $selected . '>' .
							esc_html( $label ) . '</option>';
		}

		$empty_select = empty( $linkcategories );
		if ( $empty_select || $include_blank )
			$html = '<option value="0">---</option>' . $html;

		if ( $multiple )
			$atts .= ' multiple="multiple"';

		$html = '<select name="' . $name . ( $multiple ? '[]' : '' ) . '"' . $atts . '>' .
					$html . '</select>';

		$validation_error = '';
		if ( is_a( $wpcf7_contact_form, 'WPCF7_ContactForm' ) )
			$validation_error = $wpcf7_contact_form->validation_error( $name );

		$html = '<span class="wpcf7-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

		return $html;
	}

	/* Validation filter */
	function validation_filter( $result, $tag ) {
		global $wpcf7_contact_form;

		$type = $tag['type'];
		$name = $tag['name'];
		$values = $this->get_values();

		if ( is_array( $_POST[$name] ) ) {
			foreach ( $_POST[$name] as $key => $value ) {
				$value = stripslashes( $value );
				if ( ! in_array( $value, (array) $values ) ) // Not in given choices.
					unset( $_POST[$name][$key] );
			}
		} else {
			$value = stripslashes( $_POST[$name] );
			if ( ! in_array( $value, (array) $values ) ) //  Not in given choices.
				$_POST[$name] = '';
		}

		if ( 'linkcat*' == $type ) {
			if ( empty( $_POST[$name] ) ||
				! is_array( $_POST[$name] ) && '---' == $_POST[$name] ||
				is_array( $_POST[$name] ) && 1 == count( $_POST[$name] ) && '---' == $_POST[$name][0] ) {
				$result['valid'] = false;
				$result['reason'][$name] = $wpcf7_contact_form->message( 'invalid_required' );
			}
		}

		return $result;
	}

}

?>