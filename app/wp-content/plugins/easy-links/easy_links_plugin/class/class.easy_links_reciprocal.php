<?php
class easy_links_reciprocal
{

		public function easy_links_reciprocal(){
			$this->__construct();
		}
		
		public function __construct(){
		}
		/****************************************************************************************************************************/
		/******************************************** Reciprocal Link    ***************************************************************/
		/**
		 *
		 * @parse a referer page to check a link exists
		 *
		 * @access public
		 *
		 */
		public function addLinks(){
			/*** get inactive links ***/
			$links = $this->getLinks(0);
	
			/*** loop through the links ***/
			foreach($links as $link)
			{
				/*** check if the link is on the referer page ***/
				if( $this->parsePage($link['reciprical_link_name']) == true)
				{
					/*** activate the link ***/
					$this->activateLink($link['reciprical_link_name']);
				}
			}
			/*** to tidy up, delete any remaining inactive links ***/
			$this->deleteInactiveLinks();
		}
		
		/**
		 *
		 * @parse url and search for link back
		 *
		 * @access private
		 *
		 * @param string $link
		 *
		  * @return bool
		 *
		 */
		public function parse_page($wherelink,$link){
			
			$ex = str_split($link,1);
			
			if(end($ex) == '/')
			{
				array_pop($ex);
				$link = implode($ex);
			}
				
			$dom = new domDocument;
			$html = @file_get_contents($wherelink);
			@$dom->loadHTML($html);
			$dom->preserveWhiteSpace = false;
			$links = $dom->getElementsByTagName('a');
			

			foreach ($links as $tag)
			{
			
			
				if(strpos($tag->getAttribute('href'), $link) !== FALSE)
				{
				
					return true;
				}
				else
				{
					continue;
				}
			}
			/*** if no url is found ***/
			return false;
		}
		
		/**
		 * @Get the domain part of a url
		 *
		 * @access public
		 *
		 * @param string $link
		 *
		 * @return string
		 *
		 */
		public function getDomain($link){
			/*** get the url parts ***/
			$parts = parse_url($link);
	
			/*** return the host domain ***/
			return $parts['scheme'].'://'.$parts['host'];
		}
		
		/********************************************* Reciprocal Link END **** *******************************************************/
		/***************************************************************************************************************************/
}
?>