<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Citations, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2016 Olivier Le Bris
# http://phoenix.cybride.net/
# Contributor : Pierre Van Glabeke
#
# Licensed under the Creative Commons by-nc-sa license.
# See LICENSE file or
# http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',array('CitationsBehaviors','addCss'));

class CitationsBehaviors
{
	/**
	This function add CSS file in the public header
	*/
	public static function addCss()
	{
		global $core;

		$url = $core->blog->getQMarkURL().'pf='.basename(dirname(__FILE__)).'/citations.css';

		echo '<link rel="stylesheet" media="screen" type="text/css" href="'.$url.'" />';
	}
}

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';
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
	
        global $core;
		$url = &$core->url;

		if ($w->offline)
			return;

		// prise en compte paramètre: affichage combo
    if (($w->homeonly == 1 && $core->url->type !== 'default') ||
			($w->homeonly == 2 && $core->url->type === 'default')) {
			return;
		}

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		dcCitations::formatBase(dcCitations::random());

		return $w->renderDiv($w->content_only,'citations '.$w->class,'',$res);
	}
}

// ajout de templates
$core->tpl->addValue('RandomCitations', array('dcCitations', 'tplRandom'));
