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

/** ==================================================
	administration
================================================== */
if (!defined('DC_RC_PATH')) {return;}

class adminCitations
{
	/**
	* installation du plugin
	*/
	static public function Install()
	{
		// test de possibilité d'installation
		if (!dcCitations::isAllowed()) return false;

		// création du schéma
		global $core;
        try
        {
			// création du schéma de la table
		    $_s = new dbStruct($core->con, $core->prefix);
		    require dirname(__FILE__).'/db-schema.php';

		    $si = new dbStruct($core->con, $core->prefix);
		    $changes = $si->synchronize($_s);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }

		// activation des paramètres par défaut
		pluginCitations::defaultsSettings();

		return true;
	}

	/**
	* désinstallation du plugin
	*/
	static public function Uninstall()
	{
		// désactivation du plugin et sauvegarde de toute la table
		pluginCitations::Inactivate();
		pluginCitations::Export(false);

		// suppression du schéma
		global $core;
        try
        {
	        $con = &$core->con;

			$strReq =
				'DROP TABLE '.
				$core->prefix.pluginCitations::pname();

			$rs = $con->execute($strReq);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }

		// suppression des paramètres par défaut
		pluginCitations::deleteSettings();
	}

	/**
	* export du contenu du schéma
	*/
	static public function Export($onlyblog = true, $outfile = null)
	{
		global $core;
        try
        {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// générer le contenu du fichier à partir des données
			if (isset($outfile)) $filename = $outfile;
			else
			{
				if ($onlyblog) $filename = $core->blog->public_path.'/'.$blogid.'-'.pluginCitations::pname().'.dat';
				else $filename = $core->blog->public_path.'/'.pluginCitations::pname().'.dat';
			}

			$content = '';
			$datas = dcCitations::getRawDatas($onlyblog);
			if (is_object($datas) !== FALSE)
			{
                $datas->moveStart();
                while ($datas->fetch())
                {
					$elems = array();

					// génération des élements de données
                    $elems[] = $datas->citation_id;
                    $elems[] = base64_encode($datas->author);
                    $elems[] = base64_encode($datas->content);

					// génération de la ligne de données exportés(sépareteur -> ;)
					$line = implode(";", $elems);
                    $content .= "$line\n";
				}
			}

			// écrire le contenu dans le fichier
			@file_put_contents($filename, $content);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
 	}

	/**
	* export du contenu du schéma
	*/
	static public function Import($onlyblog = true, $infile = null)
	{
		global $core;
        try
        {
			// lire le contenu du fichier à partir des données
			if (isset($infile)) $filename = $infile;
			else
			{
				if ($onlyblog) $filename = $core->blog->public_path.'/'.$blogid.'-'.pluginCitations::pname().'.dat';
				else $filename = $core->blog->public_path.'/'.pluginCitations::pname().'.dat';
			}

			// ouverture du fichier
	        $content = '';
            $fh = @fopen($filename, "r");
            if ($fh === FALSE) return false;
			else
			{
				// boucle de lecture sur les lignes du fichier
	            $err = false;
	            while (!feof($fh))
	            {
					// lecture d'une ligne du fichier
	                $l = @fgetss($fh, 4096);
	                if ($l != FALSE)
	                {
						// sécurisation du contenu de la ligne et décomposition en élements (sépareteur -> ;)
	                    $line = (string) html::clean((string) $l);
	                    $elems = explode(";", $line);

						// traitement des données lues
	                    $elem_id = $elems[0];
	                    $elem_author = base64_decode($elems[1]);
	                    $elem_content = base64_decode($elems[2]);
						dcCitations::add($elem_author, $elem_content);
					}
				}

				// fermeture du fichier
	            @fclose($fh);

	            if ($err) return false;
				else return true;
			}
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
}

/** ==================================================
	onglets de la partie d'administration
================================================== */

class tabsCitations
{
	/**
	* paramétrage du plugin
	*/
	static public function Settings()
	{
		// prise en compte du plugin installé
		if (!dcCitations::isInstalled()) return;

		global $core;
		try
		{
	        $blog = &$core->blog;
	        $auth = &$core->auth;

			// paramétrage de l'état d'activation du plugin
	        $pactive = '';
	        if (pluginCitations::isActive()) $pactive = 'checked';
			$sadmin = false;
			if ($auth->isSuperAdmin()) $sadmin = true;

	        echo
			'<div class="fieldset">' .
			'<h4>'.__('Plugin state').'</h4>'.
				'<form action="plugin.php" method="post" name="state">'.
           '<p>'.         $core->formNonce().
					form::hidden(array('p'),pluginCitations::pname()).
					form::hidden(array('op'),'state').'</p>'.
          '<p>'.
						'<label class="classic" for="active">'.form::checkbox('active',1,$pactive).__('active / inactive').'</label>'.
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Save').'" />'.
					'</p>'.
        '</form>'.
			'</div>'.

			// gestion des paramètres du plugin
			'<div class="fieldset" style="display:none;">' .
			'<h4>'.__('Settings').'</h4>'.
				'<form action="plugin.php" method="post" name="settings">'.
           '<p>'.         $core->formNonce().
					form::hidden(array('p'),pluginCitations::pname()).
					form::hidden(array('op'),'settings').'</p>'.
					'<p>'.
						'<input type="button" value="'.__('Defaults').'" onclick="pdefaults(); return false" />'.
					'</p>'.
				'</form>'.
			'</div>'.

			// export/import pour le blog
			'<div class="fieldset" style="display:none;">' .
			'<h4>'.__('Import/Export datas').'</h4>'.
				'<form action="plugin.php" method="post" name="impexp">'.
					'<p>'.$core->formNonce().
					form::hidden(array('p'),pluginCitations::pname()).
					form::hidden(array('op'),'export').
					'<label class="classic">'.form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').'</label><br />'.
					'<label class="classic">'.form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').'</label>'.
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Export').'" />'.
						'<input type="button" value="'.__('Import').'" onclick="pimport(); return false" />'.
					'</p>'.
				'</form>'.
			'</div>';
			
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* à propos du plugin
	*/
	static public function About()
	{
        echo
		'<div class="fieldset">' .
		'<h4>'.__('Why this plugin').'</h4>'.
			__('I started to collect citations from many years now.').'<br />'.
			__('When I started my blog, I looked for a way to show citations from a known list.').'<br />'.
			__('Under Dotclear 1 there where already a plugin I used, but none under Dotclear 2.').'<br />'.
			__('This is why I developped it.').
		'</div>'.
		'<div class="fieldset">' .
		'<h4>'.__('Authors').'</h4>'.
			'<ul>'.
				'<li>'.'<a href="http://phoenix.cybride.net/" title="Olivier Le Bris">Olivier Le Bris</a>'.'</li>'.
			'</ul>'.
		'</div>'.
		'<div class="fieldset">' .
		'<h4>'.__('Licence').'</h4>'.
			'<ul>'.
				'<li>'.
                    '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/2.0/fr/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/2.0/fr/88x31.png" /></a>'.
				'</li>'.
				'<li>'.
                    __('This work is released under a ').'<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/2.0/fr/">'.__('Creative Commons contract').'</a>.'.
				'</li>'.
			'</ul>'.
		'</div>'.
		'<div class="fieldset">' .
		'<h4>'.__('Greets').'</h4>'.
			'<ul>'.
				'<li>'.'<a href="mailto:webmaster@balbinus.net" title="'.__('Vincent Tabard (plugin under Dotclear 1)').'">'.__('Vincent Tabard (plugin under Dotclear 1)').'</a>'.'</li>'.
				'<li>'.__('All the peoples who tested it !').'</li>'.
			'</ul>'.
		'</div>'.
		'<div class="fieldset">' .
		'<h4>'.__('Usefull informations').'</h4>'.
			'<ul>'.
				'<li>'.__('Support:').' <a href="http://www.cybride.net/redirect/support/'.pluginCitations::pname().'" title="'.__('Clic here to go to the support.').'">'.__('Clic here to go to the support.').'</a>'.'</li>'.
				'<li>'.__('Files:').' '.__('Read autheurs.txt and changelog.txt in the doc folder.').'</a>'.'</li>'.
				'<li>'.__('Dotclear:').' <a href="http://www.dotclear.net/" title="'.__('Clic here to go to Dotclear.').'">'.__('Clic here to go to Dotclear.').'</a>'.'</li>'.
			'</ul>'.
		'</div>';
	}

	/**
	* liste les citations du blog
	*/
	static public function ListBlog()
	{
		// prise en compte du plugin installé
		if (!dcCitations::isInstalled()) return;

		$datas = dcCitations::getlist();
        if (!is_object($datas)) echo __('No citation for this blog.');
        else
        {
			global $core;

			// début du tableau et en-têtes
            echo
			'<form action="plugin.php" method="post" name="listblog">' .
				'<p>'.$core->formNonce().
				form::hidden(array('p'),pluginCitations::pname()).
				form::hidden(array('op'),'remove').
				form::hidden(array('id'),'').'</p>'.
				'<table class="clear">'.
					'<tr>'.
						'<th>&nbsp;</th>'.
						'<th class="nowrap">'.__('Author').'</th>' .
						'<th class="nowrap" colspan="2">'.__('Content').'</th>'.
					'</tr>';

			// parcours la liste pour l'affichage
            $datas->moveStart();
            while ($datas->fetch())
            {
				$k = (integer)$datas->citation_id;
				$editlink = 'onclick="ledit('.$k.'); return false"';
                $guilink = '<a href="#" '.$editlink.' title="'.__('Edit citation').'"><img src="images/edit-mini.png" alt="'.__('Edit citation').'" /></a>';

                echo
				'<tr class="line">'.
					'<td>'.form::checkbox(array('citation['.html::escapeHTML($k).']'), 1).'</td>'.
					'<td class="minimal nowrap" '.$editlink.'>'.html::escapeHTML(text::cutString($datas->author, 50)).'</td>'.
					'<td class="maximal nowrap" '.$editlink.'>'.html::escapeHTML(text::cutString($datas->content, 100)).'</td>'.
					'<td class="status">'.$guilink.'</td>'.
				'</tr>';
			}

			// fermeture du tableau
            echo
			'</table><p>'.
			'<input type="submit" value="'.__('Delete').'" />'.
			'</p></form>';
		}
	}

	/**
	* formulaire d'ajout de citation
	*/
	static public function AddEdit()
	{
		// prise en compte du plugin installé
		if (!pluginCitations::isInstalled()) return;

		global $core;
		try
		{
			$allowed = true;

			// test si ajout ou édition
			if (!empty($_POST['id']))
			{
				$id = (integer)$_POST['id'];
				$datas = dcCitations::get($id);
				if ($datas == null) $allowed = false;
				else
				{
					$author = $datas['author'];
					$content = $datas['content'];
					$form_title = __('Edit a citation');
					$form_op = 'edit';
					$form_libel = __('Update');
					$form_id = '<input type="hidden" name="id" value="'.$id.'" />';
				}
			}
			else
			{
				$id = -1;
				$author = '';
				$content = '';
				$form_title = __('Add a citation');
				$form_op = 'add';
				$form_libel = __('Save');
				$form_id = '';
			}

			if (!$allowed) echo __('Not allowed.');
			else
		        echo
				'<div class="fieldset">'.
					'<h4>'.$form_title.'</h4>'.
					'<form action="plugin.php" method="post" name="addedit">'.
						'<p>'.$core->formNonce().
						form::hidden(array('p'),pluginCitations::pname()).
						form::hidden(array('op'),$form_op).
						$form_id.'</p>'.
						'<p>'.
							'<label>'.__('Author:').'</label>'.
							form::field(array('fauthor'),50,255, $author).
							'<br /><br />'.
							'<label for="fcontent">'.__('Content:').'</label>'.
							form::textarea('fcontent',100,10,$content).
						'</p>'.
						'<p>'.
							'<input type="submit" value="'.$form_libel.'" />'.
							'<input type="reset" value="'.__('Cancel').'" />'.
						'</p>'.
					'</form>'.
				'</div>';
			}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
}
