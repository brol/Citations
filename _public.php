<?php
/**
 * @brief Citations, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Olivier Le Bris, Pierre Van Glabeke and contributors
 *
 * @copyright http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
 */
if (!defined('DC_RC_PATH')) { return; }

dcCore::app()->addBehavior('publicHeadContent',array('CitationsBehaviors','addCss'));

class CitationsBehaviors
{
	/**
	This function add CSS file in the public header
	*/
	public static function addCss()
	{

		$url = dcCore::app()->blog->getQMarkURL().'pf='.basename(dirname(__FILE__)).'/citations.css';

		echo '<link rel="stylesheet" media="screen" type="text/css" href="'.$url.'" />';
	}
}

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.plugin.php';
require_once dirname(__FILE__).'/inc/class.modele.php';
require_once dirname(__FILE__).'/_widgets.php';

// widget
class WidgetsCitations
{
	/**
	* initialisation du widget
	*/
    static public function widget($w)
    {
		// prise en compte de l'état d'activation du plugin
		if (!pluginCitations::isActive()) return;
	
		$url = &dcCore::app()->url;

		if ($w->offline)
			return;

		// prise en compte paramètre: affichage combo
    if (($w->homeonly == 1 && dcCore::app()->url->type !== 'default') ||
			($w->homeonly == 2 && dcCore::app()->url->type === 'default')) {
			return;
		}

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		dcCitations::formatBase(dcCitations::random());

		return $w->renderDiv($w->content_only,'citations '.$w->class,'',$res);
	}
}

// ajout de templates
dcCore::app()->tpl->addValue('RandomCitations', array('dcCitations', 'tplRandom'));
