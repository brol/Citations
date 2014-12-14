<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Citations, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2010 Olivier Le Bris
# http://phoenix.cybride.net/
# Contributor : Pierre Van Glabeke
#
# Licensed under the Creative Commons by-nc-sa license.
# See LICENSE file or
# http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
# -- END LICENSE BLOCK ------------------------------------

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;

// ajout des comportements
$core->addBehavior('pluginsBeforeDelete', array('dcBehaviorsCitations', 'pluginsBeforeDelete'));


// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/_widgets.php';

// intégration au menu
$_menu['Blog']->addItem(__('Citation manager'), 'plugin.php?p='.pluginCitations::pname(), pluginCitations::urldatas().'/icon.png',
    preg_match('/plugin.php\?p='.pluginCitations::pname().'(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('usage,admin', $core->blog->id));

// définition des comportements	
class dcBehaviorsCitations
{

	/**
	* avant suppression du plugin par le gestionnaire, on le déinstalle proprement
	*/
    public static function pluginsBeforeDelete($plugin)
    {
		global $core;
        try
        {
            $name = (string) $plugin['name'];
            if (strcmp($name, pluginCitations::pname()) == 0)
            {
                require dirname(__FILE__).'/class.admin.php';
                adminCitations::Uninstall();
            }
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }
}

$core->addBehavior('adminDashboardFavorites','citationsDashboardFavorites');

function citationsDashboardFavorites($core,$favs)
{
	$favs->register('citations', array(
		'title' => __('Citations'),
		'url' => 'plugin.php?p=citations',
		'small-icon' => 'index.php?pf=citations/icon.png',
		'large-icon' => 'index.php?pf=citations/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}