<?php

class PrettifyPlugin extends Plugin
{
	
	private $langs = array(
		'apollo','basic','clj','css','dart','erlang','go','hs','lisp','llvm','lua','matlab','ml','mumps','n','pascal',
		'proto','r','rd','scala','sql','tcl','tex','vb','vhdl','wiki','xq','yaml'
	);
	
	private $skins = array(
		'default','desert','doxy','bootstrap'
	);
	
	/**
	 * Make sure all content within pretty printed code blocks is html entitied
	 * and add the pretty print JS to the site footer.
	 * 
	 * @param string $content The content of the post
	 * @return string The processed content.
	 */
	public function filter_post_content_out($content)
	{
		$count = 0;
		
		$content = preg_replace_callback('%(<\s*(code|pre) [^>]*class\s*=\s*(["\'])[^\'"]*prettyprint[^\'"]*\3[^>]*>)(.*?)(</\s*(\2)\s*>)%si', array($this, 'content_callback'), $content, -1, $count);
		
		// Add the prettify JS if required
		if ( $count && !Stack::has('template_footer_javascript', 'prettify') )
		{
			$url = $this->get_url(true) . 'js/run_prettify.js';
			Stack::add('template_footer_javascript', $url.$this->build_query_string(), 'prettify');
		}
		return $content;
	}
	
	private function build_query_string() {
		$query = array();

		$langs = Options::get('prettify__langs');
		if ( $langs && count($langs) ) 
		{
			$query[] = 'lang=' . implode('&amp;lang=', $langs);
		}

		$skin = Options::get('prettify__skin');
		if ( $skin && $skin !== 'default' ) 
		{
			$query[] = 'skin=' . $skin;
		}

		if ( count($query) ) 
		{
			$query = '?' . implode('&amp;', $query);
		} 
		else 
		{
			$query = '';
		}
		return $query;
	}
	
	public function alias()
	{
		return array (
			'filter_post_content_out' => array('filter_post_content_long'),
		);
	}
	
	/**
	 * Show configure option
	 */
	public function filter_plugin_config( $actions, $plugin_id ) 
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[]= _t('Configure');
		}
		return $actions;
	}
	
	/**
	 * Handle plugin config 
	 */
	public function action_plugin_ui( $plugin_id, $action ) 
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t('Configure'):
					
					Stack::add('admin_footer_javascript', $this->get_url(true) . 'js/admin.js', 'prettify');
					
					$ui= new FormUI( strtolower( get_class( $this ) ) );
					
					$skin = $ui->append( 'fieldset', 'prettify__skin', _t( 'Skin' ) );
					$skin->append( 'select', 'skin', 'prettify__skin', _t( 'Skin' ), array_combine($this->skins, $this->skins) );
					$skin->append( 'static', '', '<iframe src="' . $this->get_url(true) . 'skin.php' . $this->build_query_string() . '" height="200" width="100%"></iframe>');
					
					$langs = $ui->append( 'fieldset', 'prettify__langs', _t( 'Language support' ) );
					$langs->append( 'static', '', _t('Pretty print will guess at what 
						language is being displayed. It does a pretty good job 
						of guessing c-like and html-like languages, but specific 
						support is avaliable for the following.'));
					$langs->append( 'checkboxes', 'langs', 'prettify__langs', _t( '' ), array_combine($this->langs, $this->langs) );
					
					$ui->append( 'submit', 'save', _t('Save') );
					$ui->out();
					
					
				break;
			}
		}
	}

	/**
	 * Enable the shortcode `code`
	 * 
	 * @param string $content The content of the shortcode, including the shortcode tags, that will be replaced.
	 * @param string $code The text of the shortcode (in this case always `code`)
	 * @param array $attrs An associative array of attributes on the shortcode
	 * @param string $context The content inside the shortcode (not including tags)
	 * @return string The text to replace the shortcode with
	 */
	public function filter_shortcode_code($content, $code, $attrs, $context)
	{
		// Ensure code tags inside the code block aren't interpreted by the browser
		$context = str_replace('</code>', '&lt/code>', $context);
		
		return '<code class="prettyprint">' . $context . '</code>';
	}
	
	/**
	 * Callback for the preg_replace which processes content to ensure code blocks are html entitied.
	 * 
	 * @param array $matches An array of matched substrings
	 * @return string Replacement string
	 */
	private function content_callback($matches)
	{
		$code = trim($matches[4], "\r\n");
		$code = str_replace( "\r\n", "\n", $code);
		$code = htmlentities($code);
		return $matches[1] . $code . $matches[5];
	}
}
?>
