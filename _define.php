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

$this->registerModule(
	/* Name */			"Citations",
	/* Description*/		"Gestionnaire de citations",
	/* Author */		"Olivier Le Bris, Pierre Van Glabeke",
	/* Version */		"1.9",
	/* Properties */
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.9',
		'support' => 'http://forum.dotclear.org/viewforum.php?id=16',
		'details' => 'http://plugins.dotaddict.org/dc2/details/citations'
		)
);