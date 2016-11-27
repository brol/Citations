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

# Enregistrement des fonctions d'exportation
$core->addBehavior('exportFull',array('citationsClass','exportFull'));
$core->addBehavior('exportSingle',array('citationsClass','exportSingle'));

class citationsClass
{
	# Full export behavior
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('citations');
	}

	# Single blog export behavior
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('citations',
			'SELECT * '.
			'FROM '.$core->prefix.'citations '.
			'WHERE blog_id = "'.$blog_id.'"'
		);
	}
}