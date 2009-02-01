<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: EchoppePotion.php 595 2008-11-09 11:21:27Z yvonnickesnault $
 * $Author: yvonnickesnault $
 * $LastChangedDate: 2008-11-09 12:21:27 +0100 (Sun, 09 Nov 2008) $
 * $LastChangedRevision: 595 $
 * $LastChangedBy: yvonnickesnault $
 */
class EchoppePotion extends Zend_Db_Table {
	protected $_name = 'echoppe_potion';
	protected $_primary = "id_echoppe_potion";

	public function findByCriteres($idRegion = null, $idEmplacement= null) {
		$db = $this->getAdapter();
		$select = $db->select();
		$select->from('echoppe_potion', '*')
		->from('type_potion')
		->from('type_qualite')
		->where('id_fk_type_potion_echoppe_potion = id_type_potion')
		->where('id_fk_type_qualite_echoppe_potion = id_type_qualite')
		->where('id_fk_echoppe_echoppe_potion = ?', $idEchoppe);
		$sql = $select->__toString();
		return $db->fetchAll($sql);
	}
}
