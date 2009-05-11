<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class CoffreMateriel extends Zend_Db_Table {
	protected $_name = 'coffre_materiel';
	protected $_primary = array('id_coffre_materiel');

	function findByIdHobbit($idHobbit) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('coffre_materiel', '*')
		->from('type_materiel', '*')
		->where('id_fk_type_coffre_materiel = id_type_materiel')
		->where('id_fk_hobbit_coffre_materiel = ?', intval($idHobbit));
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}