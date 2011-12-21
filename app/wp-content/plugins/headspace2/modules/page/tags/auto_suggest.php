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

class HS_TagSuggest
{
	var $dictionary = array ();
	
	function filter ($word) {
		if (strlen ($word) > 2 && !in_array ($word, array ('the', 'and', 'off', 'that', 'there', 'not')))
			return true;
		return false;
	}
	
	// Scans $text for appropriate tags, excluding any existing ones
	function HS_TagSuggest ($dictionary) {
		$this->dictionary = substr( $dictionary, 0, 200 );   // Memory limiter
	}
	
	function stem_words ($words) {
		$stemmer = new HS_PorterStemmer ();
		
		// Construct a stemmed dictionary
		$stemmed = array ();
		if (count ($words) > 0) {
			foreach ($words AS $word)
				$stemmed[] = $stemmer->Stem ($word);
		}
		
		return $stemmed;
	}
	
	// Look for matches of each word in the dictionary, whether direct or stemmed.  Returned the dictionary word
	function matches ($text) {
		// Strip HTML
		if (function_exists ('wp_filter_nohtml_kses'))
			$text = strtolower (stripslashes (wp_filter_nohtml_kses ($text)));
		else
			$text = strtolower (stripslashes (strip_tags ($text)));

		$text = preg_replace ('/[<>0-9\?\’“”\'\"\$\%_\[\]\(\)!:;\.,]/', '', $text);
		$text = preg_replace ('/[\/]/', ' ', $text);
		
		// Split text into an array of words
		$words = preg_split ("/[\s,]+/", $text);

		// Remove words less than 2 characters and duplicates
		$words = array_filter ($words, array (&$this, 'filter'));
		$words = array_unique ($words);
		
		$stemmed_words = $this->stem_words ($words);

		$matched = array ();
		if (strlen ($this->dictionary) > 0) {
			$stemmer = new HS_PorterStemmer ();

			$matched = apply_filters('headspace_auto_suggest', $matched, $this->dictionary, $text);
			
			if (empty ($matched)) {
				// Go through each word in the dictionary and see if we can find a match
				$word = strtok ($this->dictionary, ',');
				while ($word !== false) {
					$word = strtolower ($word);
				
					if (in_array ($word, $words) || in_array ($stemmer->Stem ($word), $stemmed_words))
						$matched[] = $word;
					
					$word = strtok (',');
				}
			}
			
			$matched = array_unique ($matched);
			sort ($matched);
		}

		return $matched;
	}
}

?>