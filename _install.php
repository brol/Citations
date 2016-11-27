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

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';
require_once dirname(__FILE__).'/class.admin.php';

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
$versionnew = $core->plugins->moduleInfo(pluginCitations::pname(), 'version');
$versionold = $core->getVersion(pluginCitations::pname());
if (version_compare($versionold, $versionnew, '>=')) return;
else
{
	// chargement des librairies
	require_once dirname(__FILE__).'/class.admin.php';
	if (adminCitations::Install())
	{
		$core->setVersion(pluginCitations::pname(), $versionnew);
		unset($versionnew, $versionold);
		return true;		
	}
	else
	{
		$core->error->add(__('Unable to install Citations.'));
		return false;
	}
}