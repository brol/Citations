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

/** administration */

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
        try
        {
			// création du schéma de la table
		    $_s = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
		    require dirname(__FILE__).'/db-schema.php';

		    $si = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
		    $changes = $si->synchronize($_s);
		}
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }

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
        try
        {
	        $con = dcCore::app()->con;

			$strReq =
				'DROP TABLE '.
				dcCore::app()->prefix.pluginCitations::pname();

			$rs = $con->execute($strReq);
        }
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }

		// suppression des paramètres par défaut
		pluginCitations::deleteSettings();
	}

	/**
	* export du contenu du schéma
	*/
	static public function Export($onlyblog = true, $outfile = null)
	{
        try
        {
			$blog = dcCore::app()->blog;
			$blogid = (string)$blog->id;

			// générer le contenu du fichier à partir des données
			if (isset($outfile)) $filename = $outfile;
			else
			{
				if ($onlyblog) $filename = dcCore::app()->blog->public_path.'/'.$blogid.'-'.pluginCitations::pname().'.dat';
				else $filename = dcCore::app()->blog->public_path.'/'.pluginCitations::pname().'.dat';
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
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }
 	}

	/**
	* export du contenu du schéma
	*/
	static public function Import($onlyblog = true, $infile = null)
	{
        try
        {
			// lire le contenu du fichier à partir des données
			if (isset($infile)) $filename = $infile;
			else
			{
				if ($onlyblog) $filename = dcCore::app()->blog->public_path.'/'.$blogid.'-'.pluginCitations::pname().'.dat';
				else $filename = dcCore::app()->blog->public_path.'/'.pluginCitations::pname().'.dat';
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
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }
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

		try
		{
	        $blog = dcCore::app()->blog;
	        $auth = dcCore::app()->auth;

			// paramétrage de l'état d'activation du plugin
	        $pactive = '';
	        if (pluginCitations::isActive()) $pactive = 'checked';
			$sadmin = false;
			if ($auth->isSuperAdmin()) $sadmin = true;

	        echo
			'<div class="fieldset">' .
			'<h4>'.__('Plugin state').'</h4>'.
				'<form action="plugin.php" method="post" name="state">'.
           '<p>'.dcCore::app()->formNonce().
					form::hidden(array('p'),pluginCitations::pname()).
					form::hidden(array('op'),'state').'</p>'.
          '<p>'.
						'<label class="classic" for="active">'.form::checkbox('active',1,$pactive).__('Enable Citation manager').'</label>'.
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
           '<p>'.dcCore::app()->formNonce().
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
					'<p>'.dcCore::app()->formNonce().
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
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }
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

			// début du tableau et en-têtes
            echo
			'<form action="plugin.php" method="post" name="listblog">' .
				'<p>'.dcCore::app()->formNonce().
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
						'<p>'.dcCore::app()->formNonce().
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
	    catch (Exception $e) { dcCore::app()->error->add($e->getMessage()); }
	}
}