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
 if (!defined('DC_RC_PATH')) {
    return;
}

// autochargement de la classe
Clearbricks::lib()->autoload([
    'dcCitations' => __DIR__ . '/inc/class.modele.php',
]);
