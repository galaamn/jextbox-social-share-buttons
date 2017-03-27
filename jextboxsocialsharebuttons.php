<?php

/**
* @package     Content - JExtBOX Social Share Buttons
* @author      Galaa
* @publisher   JExtBOX.com - BOX of Joomla Extensions (www.jextbox.com)
* @copyright   Copyright (C) 2011-2017 Galaa
* @authorUrl   http://galaa.mn
* @authorEmail contact@galaa.mn
* @license     This extension in released under the GNU/GPL License - http://www.gnu.org/copyleft/gpl.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

class plgContentJExtBOXSocialShareButtons extends JPlugin
{

	private $app, $social_metatags_added;

	public function __construct(& $subject, $config){

		define('JEXTBOX_SOCIAL_SHARE_BUTTONS_URL', JURI::base() . 'plugins/content/jextboxsocialsharebuttons/images/');
		parent::__construct($subject, $config);
		$this->app = JFactory::getApplication();
		$this->social_metatags_added = false;

	}

	public function onContentBeforeDisplay($context, &$article, &$params){

		if($this->app->isAdmin() || stripos($context, 'com_content') !== 0 || $this->params->get('position', 2) == 1){
			return '';
		}
		return $this->getButtons($article);

	}

	public function onContentAfterDisplay($context, &$article, &$params){

		if($this->app->isAdmin() || stripos($context, 'com_content') !== 0 || $this->params->get('position', 2) == 0){
			return '';
		}
		return $this->getButtons($article);

	}

	private function getButtons($article){

		// excluded view mode
		if(!$this->view()){
			return '';
		}
		// excluded categories or articles
		$categories = $this->params->get('categories',array());
		if((in_array($article->catid, $categories) && ($this->params->get('categories_option') == 'exclude')) || (!in_array($article->catid, $categories) && ($this->params->get('categories_option') == 'include'))){
			return '';
		}
		$articles = trim($this->params->get('articles'));
		if(!empty($articles)){
			$articles = explode(',', $articles);
		}
		settype($articles, 'array');
		if((in_array($article->id, $articles) && ($this->params->get('articles_option') == 'exclude')) || (!in_array($article->id, $articles) && ($this->params->get('articles_option') == 'include'))){
			return '';
		}
		// get article link and title
		$url = JURI::base();
		$url = new JURI($url);
		$root = $url->getScheme() . '://' . $url->getHost();
		$link = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid.':'.$article->category_alias));
		$link = $root . $link;
		$title = rawurlencode($article->title);
		// buttons
		$style = trim($this->params->get('button_style', ''));
		!empty($style) ? $style = ' style="' . $style . '"' : NULL;
		$buttons = array();
		$this->params->get('displayFacebook') ? array_push($buttons, $this->getFacebookButton($style, $title, $link)) : NULL;
		$this->params->get('displayTwitter') ? array_push($buttons, $this->getTwitterButton($style, $title, $link)) : NULL;
		$this->params->get('displayGooglePlus') ? array_push($buttons, $this->getGooglePlusButton()) : NULL;
		$this->params->get('displayBiznetworkMN') ? array_push($buttons, $this->getBiznetworkMNButton()) : NULL;
		$buttons = implode('&nbsp;&nbsp;', $buttons);
		// Social metatags
		if($this->params->get('social_tags', 1) && JFactory::getApplication()->input == 'article' && !$this->social_metatags_added){
			isset($article->fulltext) ? $fulltext = $article->fulltext : $fulltext = '';
			$image = $this->catch_first_image($article->introtext . $fulltext);
			if(empty($image)){
				$image = trim($this->params->get('social_tag_image', JEXTBOX_SOCIAL_SHARE_BUTTONS_URL . 'state_emblem_of_mongolia.png'));
			}
			$image = JURI::base().$image;
			$mainframe = JFactory::getApplication();
			$site_name = $mainframe->getCfg('sitename');
			$metadesc = trim($article->metadesc);
			if(empty($metadesc)){
				if($this->params->get('social_tag_description', 'from_metadesc') == 'from_metadesc'){
					$metadesc = $mainframe->getCfg('metadesc');
				}else{ // from_article
					$metadesc = mb_substr(strip_tags($article->introtext . $fulltext), 0, 160, 'utf8');
				}
			}
			$doc = JFactory::getDocument();
			$doc->addCustomTag('<meta property="og:title" content="'.$article->title.'" />');
			$doc->addCustomTag('<meta property="og:type" content="article" />');
			$doc->addCustomTag('<meta property="og:url" content="'.$link.'" />');
			$doc->addCustomTag('<meta property="og:image" content="'.$image.'" />');
			$doc->addCustomTag('<meta property="og:site_name" content="'.$site_name.'" />');
			$doc->addCustomTag('<meta property="og:description" content="'.$metadesc.'" />');
			$doc->addCustomTag('<link rel="image_src" href="'.$image.'" />');
			$doc->addCustomTag('<meta name="twitter:card" content="summary" />');
			$doc->addCustomTag('<meta name="twitter:title" content="'.$article->title.'" />');
			$doc->addCustomTag('<meta name="twitter:description" content="'.$metadesc.'" />');
			$doc->addCustomTag('<meta name="twitter:image" content="'.$image.'" />');
			$this->social_metatags_added = true;
		}
		// set title of group of buttons
		if($this->params->get('show_title')){
			$style = trim($this->params->get('title_style', ''));
			!empty($style) ? $style = ' style="' . $style . '"' : NULL;
			$title = '<div class="jextboxsocialsharebuttonstitle" id="jextboxsocialsharebuttonstitle"' . $style . '>' . $this->params->get('title') . '</div>';
		}else{
			$title = '';
		}
		// set style of main div element
		$style = trim($this->params->get('main_style', ''));
		!empty($style) ? $style = ' style="' . $style . '"' : NULL;
		// result
		return '<div class="jextboxsocialsharebuttonsbox" id="jextboxsocialsharebuttonsbox"' . $style . '>' . $title . $buttons . '</div>';

	}

	// Mongolian Sites

	private function getBiznetworkMNButton(){

		return '<script type="text/javascript">
document.write('."'".'<iframe src="http://biznetwork.mn/like/button?href='."'"." + encodeURIComponent(location.href)+ '&title=' + encodeURIComponent(document.title) +"."'".' scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:91px; height:20px;" allowTransparency="true"></iframe>'."'".');
</script>';

	}

	// Other Sites

	private function getFacebookButton($style, $title, $link){

		return '<a style="text-decoration:none !important;" href="http://www.facebook.com/sharer.php?u=' . $link . '&amp;t=' . $title . '" target="blank" ><img' . $style . ' src="' . JEXTBOX_SOCIAL_SHARE_BUTTONS_URL . 'facebook.png' . '" alt="FaceBook" /></a>';

	}

	private function getTwitterButton($style, $title, $link){

		return '<a style="text-decoration:none !important;" href="http://twitter.com/?status=' . $title . " " . $link . '" target="blank" ><img' . $style . ' src="' . JEXTBOX_SOCIAL_SHARE_BUTTONS_URL . 'twitter.png' . '" alt="Twitter" /></a>';

	}

	private function getGooglePlusButton(){

		return '<g:plusone size="medium" annotation="none"></g:plusone>'."
<script type=".'"'.'text/javascript'.'"'.">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>";

	}

	private function view(){

		$view = JFactory::getApplication()->input;
		if(($view == 'article') && ($this->params->get('show_in_full_view') == 'no')){ 
			return FALSE;
		}
		if((($view == 'featured') || ($view == 'frontpage')) && ($this->params->get('show_in_featured_view') == 'no')){ 
			return FALSE;
		}
		if(($view == 'category') && ($this->params->get('show_in_category_view') == 'no')){ 
			return FALSE;
		}
		return TRUE;

	}

	private function catch_first_image($text){

		if(preg_match('/img.*src="([^"]*)"/i', $text, $matches)){
			return $matches[1];
		}else{
			return '';
		}

	}

}
?>
