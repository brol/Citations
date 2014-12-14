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
if (!defined('DC_RC_PATH')) {return;}

// le plugin
class dcCitations
{
	/* ==================================================
		fonction techniques
	================================================== */

	/**
	* est-ce que la version de Dotclear est installée
	*/
	static public function isAllowed()
	{
		if (pluginCitations::isRelease() || pluginCitations::isBeta('7')) return true;
		else return false;
	}

	/**
	* est-ce que le plugin est installé
	*/
	static public function isInstalled() { return pluginCitations::isInstalled(); }

	/**
	* renvoit le contenu total de la table sous forme de tableau de données brutes
	* (tout blog confondu)
	*/
	static public function getRawDatas($onlyblog = false)
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$con = &$core->con;

			// requète sur les données et renvoi null si erreur
			$strReq =
				'SELECT *'.
				' FROM '.$core->prefix.pluginCitations::pname();
			
			$rs = $con->select($strReq);
			if ($rs->isEmpty()) return null;
			else return $rs;
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* renvoit le prochain id de la table
	*/
	static public function nextid()
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = (string)$blog->id;

			// requète sur les données et renvoi un entier
			$strReq =
				'SELECT max(citation_id)'.
				' FROM '.$core->prefix.pluginCitations::pname().
				' WHERE blog_id=\''.$blogid.'\'';
				
			$rs = $con->select($strReq);
			if ($rs->isEmpty()) return 0;
			else return ((integer)$rs->f(0)) +1;
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* renvoit un id pris au hasard dans la table
	*/
	static public function randomid()
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = (string)$blog->id;

			// requète sur les données et renvoi un entier
			$strReq =
    			'SELECT min(citation_id), max(citation_id)'.
    			' FROM '.$core->prefix.pluginCitations::pname().
    			' WHERE blog_id=\''.$blogid.'\'';
			
			$rs = $con->select($strReq);
			if ($rs->isEmpty()) return 0;
			else return rand($rs->f(0), $rs->f(1));
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* récupère une citation par son id
	*/
	static public function get($id = -1)
	{
		// test sur l'id qui doit être numérique
		if (!is_numeric($id)) return null;

		// test sur la valeur de l'id qui doit être positive ou null
		else if ($id < 0) return null;

		// récupère la citation
		else
		{
			global $core;
	        try
	        {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = (string)$blog->id;

				// requète sur les données et renvoi null si erreur
	            $strReq =
	    			'SELECT author,content' .
	    			' FROM '.$core->prefix.pluginCitations::pname().
	    			' WHERE citation_id='.$id.' AND blog_id=\''.$blogid.'\'';
	            
				$rs = $con->select($strReq);
	            if ($rs->isEmpty()) return null;
				else
				{
					// mise en forme des données
					$result = array();
		            $result['id'] = (integer)$id;
		            $result['author'] = html::clean((string)$rs->f('author'));
		            $result['content'] = html::clean((string)$rs->f('content'));
		            return $result;
				}
	        }
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* ajoute une citation
	*/
	static public function add($_author = null, $_content = null)
	{
		// test des paramètres
		if ($_author == null || $_content == null) return null;

		// met à jour la citation
		else
		{
			global $core;
	        try
	        {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// nettoyage et sécurisation des données saisies
				$author = $con->escape(html::escapeHTML(html::clean($_author)));
				$content = $con->escape(html::escapeHTML(html::clean($_content)));

				// requète sur les données et renvoi un booléen
	            $strReq =
	    			'INSERT INTO '.$core->prefix.pluginCitations::pname().
	    			' (citation_id, blog_id, author, content)'.
	    			' VALUES (\''.self::nextid().'\', \''.$blogid.'\', \''.$author.'\', \''.$content.'\')';
	            
				if ($con->execute($strReq)) return true;
	            else return false;
	        }
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* met à jour une citation par son id
	*/
	static public function update($id = -1, $_author = null, $_content = null)
	{
		// test des paramètres
		if (self::get($id) == null || $_author == null || $_content == null) return null;

		// met à jour la citation
		else
		{
			global $core;
	        try
	        {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// nettoyage et sécurisation des données saisies
				$author = $con->escape(html::escapeHTML(html::clean($_author)));
				$content = $con->escape(html::escapeHTML(html::clean($_content)));

				// requète sur les données et renvoi un booléen
	            $strReq =
	    			'UPDATE '.$core->prefix.pluginCitations::pname().
	    			' SET author=\''.$author.'\', content=\''.$content.'\''.
	    			' WHERE citation_id='.$id.' AND blog_id=\''.$blogid.'\'';
	            
				if ($con->execute($strReq)) return true;
	            else return false;
	        }
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* supprime une citation par son id
	*/
	static public function delete($id = -1)
	{
		// test des paramètres
		if (self::get($id) == null) return null;

		// supprime la citation
		else
		{
			global $core;
	        try
	        {
				$blog = &$core->blog;
				$con = &$core->con;
				$blogid = $con->escape((string)$blog->id);

				// requète sur les données et renvoi un booléen
	            $strReq =
	    			'DELETE FROM '.$core->prefix.pluginCitations::pname().
	    			' WHERE citation_id='.$id.' AND blog_id=\''.$blogid.'\'';
	            
				if ($con->execute($strReq)) return true;
	            else return false;
	        }
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
		}
	}

	/**
	* renvoit le contenu de la table sous forme de tableau de données brutes
	*/
	static public function getlist()
	{
		global $core;
		try
		{
			$blog = &$core->blog;
			$con = &$core->con;
			$blogid = $con->escape((string)$blog->id);

			// requète sur les données et renvoi null si erreur
			$strReq =
				'SELECT *'.
				' FROM '.$core->prefix.pluginCitations::pname().
				' WHERE blog_id=\''.$blogid.'\'';
			
			$rs = $con->select($strReq);
			if ($rs->isEmpty()) return null;
			else return $rs;
		}
		catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* récupère une citation au hasard
	*/
	static public function random() { return self::get(self::randomid()); }

	/**
	* renvoi un contenu formaté pour l'affichage de la citation
	*/
    static public function formatBase($citation)
    {
        if (is_array($citation))
            return
			'<p><span class="contenu">'.$citation['content'].'</span>'.
			'<span class="auteur">'.$citation['author'].'</span></p>';
        else
            return '';
	}

	/**
	* renvoi un contenu formaté pour l'affichage de la citation
	*/
    static public function format($citation)
    {
        if (is_array($citation)) return '<div class="citations">'.self::formatBase($citation).'</div>';
        else return '';
    }

	/**
	* renvoi une citation formatée au prise au hasard
	*/
    static public function randomFormat()
    {
        if (pluginCitations::isActive()) return self::format(self::random());
        else return '';
    }

	/* ==================================================
		templates
	================================================== */

	/**
	* affiche une citation aléatoire (formatté)
	*/
    static public function tplRandom() { return '<?php echo dcCitations::randomFormat(); ?>'; }
}
