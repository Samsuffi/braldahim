<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision:  $
 * $LastChangedBy: $
 */
class AncienHobbit extends Zend_Db_Table {
	protected $_name = 'ancien_hobbit';
	protected $_primary = 'id_ancien_hobbit';

	public function findById($id){
		$where = $this->getAdapter()->quoteInto('id_hobbit_ancien_hobbit = ?',(int)$id);
		return $this->fetchRow($where);
	}
}