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

if (!defined('DC_RC_PATH')) { return; }

// initialisation du widget
$core->addBehavior('initWidgets', array('dcWidgetCitations', 'widget'));
class dcWidgetCitations
{

	/**
	* initialisation du widget
	*/
    public static function widget($widgets)
    {
		global $core, $plugin_name;
        try
        {
            $widgets->create(pluginCitations::pname(), __('Citations'), array('WidgetsCitations', 'widget'),
			null,
			__('Displaying a random quote'));

			// ATTENTION: modifier le nom du widget
            $widgets->citations->setting('title', __('Title:'), __('Citations'));
        		$widgets->citations->setting('homeonly',__('Display on:'),0,'combo',
        			array(
        				__('All pages') => 0,
        				__('Home page only') => 1,
        				__('Except on home page') => 2
        				)
        		);
        		$widgets->citations->setting('content_only',__('Content only'),0,'check');
        		$widgets->citations->setting('class',__('CSS class:'),'');
		        $widgets->citations->setting('offline',__('Offline'),0,'check');
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }
}