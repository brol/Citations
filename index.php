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

if (!defined('DC_CONTEXT_ADMIN')) exit;
dcPage::check('usage,admin');

$page_title = __('Citation manager');

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';
require_once dirname(__FILE__).'/class.admin.php';

// paramétrage des variables
$plugin_name = __('Citations');
$plugin_tab = 'tab_settings';
$id = null;

// récupération de l'onglet (si disponible)
if (!empty($_POST['tab'])) $plugin_tab = 'tab_'.(string)$_POST['tab'];
else if (!empty($_GET['tab'])) $plugin_tab = 'tab_'.(string)$_GET['tab'];

// récupération de l'opération (si possible)
if (!empty($_POST['op'])) $plugin_op = (string)$_POST['op'];
else $plugin_op = 'none';

// action en fonction de l'opération
switch ($plugin_op)
{

	// modification du paramétrage
  case 'settings':
	{
		$plugin_tab = 'tab_settings';
	}
	break;
	
	// modification de l'état d'activation du plugin
 	case 'state':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['active']))
	            pluginCitations::Activate();
	        else
	            pluginCitations::Inactivate();

			// notification de modification au blog et redirection
			$core->blog->triggerBlog();
			pluginCitations::redirect(pluginCitations::admin().'&tab=settings&msg='.rawurldecode(__('Settings updated.')));
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// vérification de mise à jour
	//case 'update':
	//{
	//	$plugin_tab = 'tab_settings';

	//    try
	//    {
	//		$msg = pluginCitations::htmlNewVersion(true);
	//	}
	//    catch (Exception $e) { $core->error->add($e->getMessage()); }
	//}
	//break;

	// ajout d'une citation
	case 'add':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        if (!empty($_POST['fauthor'])) $author = $_POST['fauthor'];
	        else $author = null;

	        if (!empty($_POST['fcontent'])) $content = $_POST['fcontent'];
	        else $content = null;

			if ($author == null || $content == null) $msg = __('Missing informations.');
			else
			{
				if (!dcCitations::add($author, $content)) $msg = __('Error adding citation.');
				else $msg = __('Citation added.');
			}
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// mise à jour d'une citation
	case 'edit':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        if (!empty($_POST['id'])) $id = $_POST['id'];
	        else $id = null;

	        if (!empty($_POST['fauthor'])) $author = $_POST['fauthor'];
	        else $author = null;

	        if (!empty($_POST['fcontent'])) $content = $_POST['fcontent'];
	        else $content = null;

			if ($author == null || $content == null)
			{
				if ($id == null) $msg = __('Missing informations.');
				else $plugin_tab = 'tab_addedit';
			}
			else
			{
				if (!dcCitations::update($id, $author, $content)) $msg = __('Error updating citation.');
				else $msg = __('Citation updated.');
			}
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// suppression d'une ou plusieurs citations
	case 'remove':
	{
		$plugin_tab = 'tab_listblog';

	    try
	    {
	        $n = 0;
	        foreach (array_keys($_POST['citation']) as $id)
	        {
	            if (dcCitations::delete($id) === TRUE) $n++;
	        }

	        if ($n <= 0) $msg = __('No citation deleted.');
	        else $msg = __('Citation(s) successfully deleted.');
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// export des données
	case 'export':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['type'])) $type = $_POST['type'];
	        else $type = 'blog';
		
			adminCitations::Export( ($type=='blog') ? true : false );
			$msg = __('Datas exported.');
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	// import des données
	case 'import':
	{
		$plugin_tab = 'tab_settings';

	    try
	    {
	        if (!empty($_POST['type'])) $type = $_POST['type'];
	        else $type = 'blog';
		
			adminCitations::Import( ($type=='blog') ? true : false );
			$msg = __('Datas imported.');
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
	break;

	case 'none':
	default:
		break;
}

// message à  afficher (en cas de redirection)
if (!empty($_GET['msg'])) $msg = (string) rawurldecode($_GET['msg']);
?>
<html>
<head>
  <title><?php echo $page_title; ?></title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=citations/style.css" />
	<?php echo dcPage::jsPageTabs($plugin_tab); ?>
	<script type="text/javascript" src="<?php echo pluginCitations::urldatas() ?>/functions.js"></script>
</head>
<body><?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($msg)) {
  dcPage::success($msg);
}
?>
	<div class="multi-part" id="tab_settings" title="<?php echo __('Settings'); ?>"><?php tabsCitations::Settings() ?></div>
	<div class="multi-part" id="tab_listblog" title="<?php echo __('List'); ?>"><?php tabsCitations::ListBlog() ?></div>
	<div class="multi-part" id="tab_addedit" title="<?php echo ($id != null) ? __('Edit') : __('Add'); ?>"><?php tabsCitations::AddEdit() ?></div>
<?php dcPage::helpBlock('citations'); ?>
</body>
</html>