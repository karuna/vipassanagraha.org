<?php
/**
 * Plugin Helper File
 *
 * @package         Modals
 * @version         4.1.1
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2012 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Load common functions
require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/tags.php';
require_once JPATH_PLUGINS . '/system/nnframework/helpers/protect.php';

/**
 * Plugin that replaces stuff
 */
class plgSystemModalsHelper
{
	function __construct(&$params)
	{
		$this->params = $params;

		$this->hasitems = 0;
		$this->itemid = 0;

		$this->comment_start = '<!-- START: Modals -->';
		$this->comment_end = '<!-- END: Modals -->';

		$bts = '((?:<(?:p|span|div)(?:(?:\s|&nbsp;)[^>]*)?>\s*){0,3})'; // break tags start
		$bte = '((?:\s*</(?:p|span|div)>){0,3})'; // break tags end

		$this->params->tag = preg_replace('#[^a-z0-9-_]#si', '', $this->params->tag);
		$this->params->tag_content = preg_replace('#[^a-z0-9-_]#si', '', $this->params->tag_content);

		$this->params->regex = '#'
			. $bts
			. '\{' . $this->params->tag . '(?:\s|&nbsp;)+'
			. '((?:[^\}]*?\{[^\}]*?\})*[^\}]*?)'
			. '\}'
			. $bte
			. '(\s*.)'
			. '#s';
		$this->params->regex_end = '#'
			. $bts
			. '\{/' . $this->params->tag . '\}'
			. $bte
			. '#s';
		$this->params->regex_inlink = '#'
			. '<a(?:\s|&nbsp;)([^>]*)>\s*((?:<img(?:\s|&nbsp;)[^>]*>\s*)*(?:<span[^>]*>\s*)*)'
			. '\{' . $this->params->tag
			. '((?:(?:\s|&nbsp;)+(?:[^\}]*?\{[^\}]*?\})*[^\}]*?)?)'
			. '\}'
			. '(.*?)'
			. '\{/' . $this->params->tag . '\}'
			. '((?:\s*</span>)*(?:\s*<img(?:\s|&nbsp;)[^>]*>\s*)*)\s*</a>'
			. '#s';

		$this->params->class = 'modal_link';
		$this->params->classnames = array_filter(explode(',', str_replace(' ', '', trim($this->params->classnames))));

		$this->params->mediafiles = explode(',', $this->params->mediafiles);
		$this->params->auto_group_id = uniqid('gallery_');

		$this->paramNamesCamelcase = array(
			'innerWidth', 'innerHeight', 'initialWidth', 'initialHeight', 'maxWidth', 'maxHeight',
		);
		$this->paramNamesLowercase = array_map('strtolower', $this->paramNamesCamelcase);
		$this->paramNamesBooleans = array(
			'scalephotos', 'scrolling', 'inline', 'iframe', 'fastiframe', 'photo', 'preloading', 'retinaimage', 'open', 'returnfocus', 'trapfocus', 'reposition', 'loop', 'slideshow', 'slideshowauto', 'overlayclose', 'esckey', 'arrowkey', 'fixed'
		);

		if (JFactory::getApplication()->input->getInt('ml', 0)) {
			JFactory::getApplication()->input->set('tmpl', 'modal');
		}
	}

	/* onAfterDispatch */
	function onAfterDispatch()
	{
		// PDF
		if (JFactory::getDocument()->getType() == 'pdf') {
			$buffer = JFactory::getDocument()->getBuffer('component');
			if (is_array($buffer)) {
				if (isset($buffer['component'], $buffer['component'][''])) {
					if (isset($buffer['component']['']['component'], $buffer['component']['']['component'][''])) {
						$this->replace($buffer['component']['']['component']['']);
					} else {
						$this->replace($buffer['component']['']);
					}
				} else if (isset($buffer['0'], $buffer['0']['component'], $buffer['0']['component'][''])) {
					if (isset($buffer['0']['component']['']['component'], $buffer['0']['component']['']['component'][''])) {
						$this->replace($buffer['component']['']['component']['']);
					} else {
						$this->replace($buffer['0']['component']['']);
					}
				}
			} else {
				$this->replace($buffer);
			}
			JFactory::getDocument()->setBuffer($buffer, 'component');
			return;
		}

		// only in html
		if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed') {
			return;
		}

		$buffer = JFactory::getDocument()->getBuffer('component');

		if (empty($buffer) || is_array($buffer)) {
			return;
		}

		// do not load scripts/styles on print page or on popup
		if (JFactory::getDocument()->getType() !== 'feed'
			&& !JFactory::getApplication()->input->getInt('print', 0)
			&& !JFactory::getApplication()->input->getInt('ml', 0)
		) {
			if ($this->params->load_jquery) {
				if( version_compare( JVERSION, '3', '<' ) ) {
					JHtml::script('modals/jquery.min.js', false, true);
				} else {
					JHtml::_('jquery.framework');
				}
			}
			JHtml::script('modals/jquery.colorbox-min.js', false, true);
			JHtml::script('modals/script.min.js', false, true);

			$defaults = $this->setDefaults();
			$defaults[] = "current: '" . JText::sprintf('MDL_MODALTXT_CURRENT', '{current}', '{total}') . "'";
			$defaults[] = "xhrError: '" . JText::_('MDL_MODALTXT_XHRERROR') . "'";
			$defaults[] = "imgError: '" . JText::_('MDL_MODALTXT_IMGERROR') . "'";
			$script = "
				var modal_class = '" . $this->params->class . "';
				var modal_defaults = { " . implode(',', $defaults) . " };
			";
			JFactory::getDocument()->addScriptDeclaration('/* START: Modals scripts */ ' . preg_replace('#\n\s*#s', ' ', trim($script)) . ' /* END: Modals scripts */');

			if ($this->params->load_stylesheet) {
				JHtml::stylesheet('modals/' . $this->params->style . '.min.css', false, true);
			}
		}

		$this->replace($buffer);

		JFactory::getDocument()->setBuffer($buffer, 'component');
	}

	/* onAfterRender */
	function onAfterRender()
	{
		// only in html and feeds
		if (JFactory::getDocument()->getType() !== 'html' && JFactory::getDocument()->getType() !== 'feed') {
			return;
		}

		$html = JResponse::getBody();
		if ($html == '') {
			return;
		}

		if (strpos($html, '{' . $this->params->tag) === false) {
			if (!$this->hasitems) {
				// remove style and script if no items are found
				$html = preg_replace('#\s*<' . 'link [^>]*href="[^"]*/(modals/css|css/modals)/[^"]*\.css[^"]*"[^>]* />#s', '', $html);
				$html = preg_replace('#\s*<' . 'script [^>]*src="[^"]*/(modals/js|js/modals)/[^"]*\.js[^"]*"[^>]*></script>#s', '', $html);
				$html = preg_replace('#/\* START: Modals .*?/\* END: Modals [a-z]* \*/\s*#s', '', $html);
			}
		} else {
			if (!(strpos($html, '<body') === false) && !(strpos($html, '</body>') === false)) {
				$html_split = explode('<body', $html, 2);
				$body_split = explode('</body>', $html_split['1'], 2);

				// only do stuff in body
				$this->replace($body_split['0']);

				$html_split['1'] = implode('</body>', $body_split);
				$html = implode('<body', $html_split);
			} else {
				$this->replace($html);
			}
		}
		$this->cleanLeftoverJunk($html);

		JResponse::setBody($html);
	}

	function setDefaults()
	{
		$keyvals = array(
			'opacity' => 0.9,
			'width' => '',
			'height' => '',
			'initialWidth' => 600,
			'initialHeight' => 450,
			'maxWidth' => false,
			'maxHeight' => false,
		);
		$defaults = array();
		foreach ($keyvals as $key => $default) {
			$k = strtolower($key);
			if (isset($this->params->{$k}) && $this->params->{$k} != $default) {
				$v = $this->params->{$k};
				if(in_array($k, $this->paramNamesBooleans)) {
					$v = (!$v || $v == 'false') ? 'false' : 'true';
				}
				$defaults[] = $key . ": '" . $v . "'";
			}
		}
		return $defaults;
	}

	function replace(&$str)
	{
		if (!is_string($str) || $str == '') {
			return;
		}

		$this->protect($str);

		if (
			(
				!empty($this->params->classnames)
				&& preg_match('#(' . implode('|', $this->params->classnames) . ')#', $str)
			)
		) {

			$regex = '#<a\s[^>]*href\s*=[^>]*>#';
			if (preg_match_all($regex, $str, $matches, PREG_SET_ORDER) > 0) {
				foreach ($matches as $match) {
					// get the link attributes
					$link = $this->getLinkAttributes($match['0']);
					// return if the link has no href
					if (!empty($link->href)) {
						continue;
					}
					// return if the link already has the Modals main class
					if (!empty($link->class) && in_array($this->params->class, explode(' ', $link->class))) {
						continue;
					}
					$pass = 0;
					$data = array();
					$isexternal = $this->isExternal($link->href);
					// check for classnames, external sites and target blanks
					if (
						(
							!empty($link->class)
							&& !empty($this->params->classnames)
							&& array_intersect($this->params->classnames, explode(' ', trim(str_replace($this->params->class, '', $link->class))))
						)
					) {
						$pass = 1;
					}

					if ($pass) {
						$this->hasitems = 1;
						$ismedia = $this->isMedia($link->href);
						if ($isexternal) {
							$data['iframe'] = 'true';
							$data['width'] = $this->params->externalwidth;
							$data['height'] = $this->params->externalheight;
						} else if ($link->href['0'] != '#' && !$ismedia) {
							$this->addTmpl($link->href);
						}

						$link->class = !empty($link->class) ? $link->class . ' ' . $this->params->class : $this->params->class;
						$link = $this->buildLink($link, $data);
						$str = NNText::strReplaceOnce($match['0'], $link, $str);
					}
				}
			}
		}

		// tag syntax inside links
		if (preg_match_all($this->params->regex_inlink, $str, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $match) {
				$this->hasitems = 1;
				$data = preg_replace('#^(\s|&nbsp;)*#', '', $match['3']);
				$link = $this->getLink($data, $match['1']);
				$html = $link . $match['2'] . $match['4'] . $match['5'] . '</a>';
				$str = NNText::strReplaceOnce($match['0'], $html, $str);
			}
		}

		// tag syntax
		if (preg_match_all($this->params->regex, $str, $matches, PREG_SET_ORDER) > 0) {
			$this->hasitems = 1;
			foreach ($matches as $match) {
				list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['3']);
				$hascontent = trim($match['4']) != '{';
				$link = $this->getLink($match['2'], '', $hascontent);

				$html = $post . $pre . $link . $match['4'];
				$str = NNText::strReplaceOnce($match['0'], $html, $str);
			}
		}

		// closing tag
		if (preg_match_all($this->params->regex_end, $str, $matches, PREG_SET_ORDER) > 0) {
			foreach ($matches as $match) {
				list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['2']);
				$html = $pre . '</a>' . $post;
				$str = NNText::strReplaceOnce($match['0'], $html, $str);
			}
		}


		$this->unprotect($str);
	}

	function getLink($str, $link = '', $hascontent = 1)
	{
		$html = '';
		$l = new stdClass;
		$l->href = '';
		$l->class = $this->params->class;
		$l->id = '';

		if ($link) {
			$link = $this->getLinkAttributes(trim($link));
			foreach ($link as $k => $v) {
				$k = trim($k);
				if ($k == 'class') {
					$l->{$k} = trim($l->{$k} . ' ' . trim($v));
				} else {
					$l->{$k} = trim($v);
				}
			}
		}

		// map href to url
		$str = preg_replace('#^href=#', 'url=', $str);

		if ($l->href) {
			$tag = NNTags::getTagValues($str, array(), '|', '=');
		} else {
			$tag = NNTags::getTagValues($str, array('url'), '|', '=');
		}

		$data = array();
		if (!empty($tag->url)) {
			$l->href = $tag->url;
		}
		unset($tag->url);


		$l->id = !empty($tag->id) ? ' id="' . $tag->id . '"' : '';
		unset($tag->id);

		$l->class .= !empty($tag->class) ? ' ' . $tag->class : '';
		unset($tag->class);

		// set data by keys set in tag without values (and see them as true)
		foreach ($tag->params as $k) {
			$data[strtolower($k)] = 'true';
		}
		unset($tag->params);

		// set data defaults
		if ($l->href) {
		}

		// set data by values set in tag
		foreach ($tag as $k => $v) {
			$data[strtolower($k)] = $v;
		}

		return $html . $this->buildLink($l, $data, $hascontent);
	}

	function getLinkAttributes($str)
	{
		$p = new stdClass;

		if (!$str) {
			return $p;
		}

		if (preg_match_all('#([a-z0-9_-]+)="([^\"]*)"#si', $str, $params, PREG_SET_ORDER) > 0) {
			foreach ($params as $param) {
				$p->$param['1'] = $param['2'];
			}
		}

		return $p;
	}

	function buildLink($l, $data, $hascontent = 1)
	{
		$html = array();


		$isexternal = $this->isExternal($l->href);
		$ismedia = $this->isMedia($l->href);

		// Force/overrule certain data values
		if ($isexternal && !$ismedia) {
			// use iframe mode for external urls
			$data['iframe'] = 'true';
			$data['width'] = !empty($data['width']) ? $data['width'] : '95%';
			$data['height'] = !empty($data['height']) ? $data['height'] : '95%';
		}

		if ($l->href && $l->href['0'] != '#' && !$isexternal && !$ismedia) {
			$this->addTmpl($l->href, (isset($data['iframe']) ? $data['iframe'] : 0));
		}

		if ($ismedia && $this->params->auto_titles && !isset($data['title'])) {
			$data['title'] = $this->getTitle($l->href);
		}


		$html[] = '<a';
		foreach ($l as $k => $v) {
			if (!empty($v)) {
				$html[] = ' ' . $k . '="' . trim($v) . '"';
			}
		}
		$html[] = $this->getDataAttribs($data);
		$html[] = '>';

		return implode('', $html);
	}


	function getDataAttribs(&$dat)
	{
		if(isset($dat['width'])) {
			unset($dat['externalWidth']);
		}
		if(isset($dat['height'])) {
			unset($dat['externalHeight']);
		}
		$data = array();
		foreach ($dat as $k => $v) {
			if ($k == '' || $v == '') {
				continue;
			}

			$k = $k == 'externalWidth' ? 'width' : $k;
			$k = $k == 'externalHeight' ? 'height' : $k;

			if ($k != 'title'
				&& $k != 'iframe'
				&& strpos($k, 'width') === false
				&& strpos($k, 'height') === false
			) {
				continue;
			}
				if (($k == 'width' || $k == 'height') && strpos($v, '%') === false) {
					// set param to innerWidth/innerHeight if value of width/height is a percentage
					$k = 'inner-' . $k;
				} else if (in_array(strtolower($k), $this->paramNamesLowercase)) {
					// fix use of lowercase params that should contain uppercase letters
					$k = $this->paramNamesCamelcase[array_search(strtolower($k), $this->paramNamesLowercase)];
					$k = strtolower(preg_replace('#([A-Z])#', '-\1', $k));
				}
			$data[] = 'data-modal-' . $k . '="' . str_replace('"', '\\"', $v) . '"';
		}

		return empty($data) ? '' : ' ' . implode(' ', $data);
	}

	function protect(&$str)
	{
		NNProtect::protectForm($str, array('{' . $this->params->tag, '{/' . $this->params->tag, '{' . $this->params->tag_content, '{/' . $this->params->tag_content));
	}

	function unprotect(&$str)
	{
		NNProtect::unprotectForm($str, array('{' . $this->params->tag, '{/' . $this->params->tag, '{' . $this->params->tag_content, '{/' . $this->params->tag_content));
	}

	/**
	 * Just in case you can't figure the method name out: this cleans the left-over junk
	 */
	function cleanLeftoverJunk(&$str)
	{
		NNProtect::removeInlineComments($str, 'Modals');
	}

	function isExternal($url)
	{
		$external = 0;
		if (!(strpos($url, '://') === false)) {
			// hostname: give preference to SERVER_NAME, because this includes subdomains
			$hostname = ($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
			$external = !(strpos(preg_replace('#^.*?://#', '', $url), $hostname) === 0);
		}
		return $external;
	}

	function isMedia($url, $filetypes = array(), $ignore = 0)
	{
		$filetype = $this->getFiletype($url);
		if (!$filetype) {
			return 0;
		}
		if (empty($filetypes)) {
			$filetypes = $this->params->mediafiles;
			$ignore = 0;
		}

		$pass = in_array($filetype, $filetypes);

		return $ignore ? !$pass : $pass;
	}

	function getFiletype($url)
	{
		$info = pathinfo($url);
		if (isset($info['extension'])) {
			$ext = explode('?', $info['extension']);
			return $ext['0'];
		}
		return '';
	}

	function getTitle($url)
	{
		$title = basename($url);
		$title = explode('.', $title);
		$title = $title['0'];
		$title = preg_replace('#[_-]([0-9]+|[a-z])$#i', '', $title);
		return ucwords(str_replace(array('-', '_'), ' ', $title));
	}

	function passURLs($url)
	{
		$pass = 0;
		$selection = explode("\n", trim(str_replace("\r", '', $this->params->urls)));
		foreach ($selection as $s) {
			$s = trim($s);
			if ($s != '') {
				if ($this->params->urls_regex) {
					$url_part = str_replace(array('#', '&amp;'), array('\#', '(&amp;|&)'), $s);
					$s = '#' . $url_part . '#si';
					if (@preg_match($s . 'u', $url)
						|| @preg_match($s . 'u', html_entity_decode($url, ENT_COMPAT, 'UTF-8'))
							|| @preg_match($s, $url)
								|| @preg_match($s, html_entity_decode($url, ENT_COMPAT, 'UTF-8'))
					) {
						$pass = 1;
						break;
					}
				} else {
					if (!(strpos($url, $s) === false)
						|| !(strpos(html_entity_decode($url, ENT_COMPAT, 'UTF-8'), $s) === false)
					) {
						$pass = 1;
						break;
					}
				}
			}
		}

		return $pass;
	}

	function addTmpl(&$url, $iframe = 0)
	{
		$url = explode('#', $url, 2);
		if (strpos($url['0'], 'ml=1') === false) {
			$url['0'] .= (strpos($url['0'], '?') === false) ? '?ml=1' : '&amp;ml=1';
		}
		if ($iframe) {
			$url['0'] .= (strpos($url['0'], '?') === false) ? '?iframe=1' : '&amp;iframe=1';
		}
		$url = JRoute::_(implode('#', $url));
	}
}
