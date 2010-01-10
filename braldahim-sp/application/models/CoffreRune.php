<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: CoffreRune.php 1974 2009-09-03 10:28:09Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2009-09-03 12:28:09 +0200 (jeu., 03 sept. 2009) $
 * $LastChangedRevision: 1974 $
 * $LastChangedBy: yvonnickesnault $
 */
class CoffreRune extends Zend_Db_Table {
	protected $_name = 'coffre_rune';
	protected $_primary = array('id_coffre_rune', 'id_fk_hobbit_coffre_rune');

	function findByIdHobbit($idHobbit, $identifiee = null, $ordre = null) {
		$whereIdentifiee = "";
		if ($identifiee != null) {
			$whereIdentifiee = " AND est_identifiee_rune = '".$identifiee."'";
		}
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_rune', '*')
		->from('type_rune', '*')
		->from('rune', '*')
		->where('id_rune_coffre_rune = id_rune')
		->where('id_fk_hobbit_coffre_rune = '.intval($idHobbit))
		->where('id_fk_type_rune = id_type_rune'.$whereIdentifiee);
		if ($ordre != null) {
			$select->order($ordre);
		}
		$sql = $select->__toString();

		return $db->fetchAll($sql);
	}

	function countByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_rune', 'count(*) as nombre')
		->where('id_fk_hobbit_coffre_rune = '.intval($idHobbit));
		$sql = $select->__toString();
		$resultat = $db->fetchAll($sql);

		$nombre = $resultat[0]["nombre"];
		return $nombre;
	}
}