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

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;

l10n::set(dirname(__FILE__).'/locales/'.dcCore::app()->lang.'/admin');

// ajout des comportements
dcCore::app()->addBehavior('pluginsBeforeDelete', array('dcBehaviorsCitations', 'pluginsBeforeDelete'));

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.plugin.php';
require_once dirname(__FILE__).'/_widgets.php';

// Admin sidebar menu
dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Citation manager'),
    dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    dcPage::getPF(basename(__DIR__) . '/icon.png'),
    preg_match(
        '/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
		dcAuth::PERMISSION_USAGE,
        dcAuth::PERMISSION_CONTENT_ADMIN,
    ]), dcCore::app()->blog->id)
);

// définition des comportements	
class dcBehaviorsCitations
{
	/**
	* avant suppression du plugin par le gestionnaire, on le déinstalle proprement
	*/
    public static function pluginsBeforeDelete($plugin)
    {
        try
        {
            $name = (string) $plugin['name'];
            if (strcmp($name, pluginCitations::pname()) == 0)
            {
                require dirname(__FILE__).'/inc/class.admin.php';
                adminCitations::Uninstall();
            }
        }
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }
    }
}

// Admin dashbaord favorite
dcCore::app()->addBehavior('adminDashboardFavoritesV2', function ($favs) {
    $favs->register(basename(__DIR__), [
        'title'       => __('Citations'),
        'url'         => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
        'small-icon'  => dcPage::getPF(basename(__DIR__) . '/icon.png'),
        'large-icon'  => dcPage::getPF(basename(__DIR__) . '/icon-big.png'),
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
    ]);
});

# Enregistrement des fonctions d'exportation
dcCore::app()->addBehavior('exportFull',array('citationsClass','exportFull'));
dcCore::app()->addBehavior('exportSingle',array('citationsClass','exportSingle'));

class citationsClass
{
	# Full export behavior
	public static function exportFull($exp)
	{
		$exp->exportTable('citations');
	}

	# Single blog export behavior
	public static function exportSingle($exp,$blog_id)
	{
		$exp->export('citations',
			'SELECT * '.
			'FROM '.dcCore::app()->prefix.'citations '.
			'WHERE blog_id = "'.$blog_id.'"'
		);
	}
}