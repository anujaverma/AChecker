<?php
/************************************************************************/
/* AChecker                                                             */
/************************************************************************/
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

/**
* DAO for "error type" table
* @access	public
* @author	Anirudh Subramanian
* @package	DAO
*/

if (!defined('AC_INCLUDE_PATH')) exit;

require_once(AC_INCLUDE_PATH. 'classes/DAO/DAO.class.php');
require_once(AC_INCLUDE_PATH. 'classes/Utility.class.php');

class ErrorTypeGroupsDAO extends DAO {

	/**
	* Return array of error type groups info whose name is NOT null
	* @access  public
	* @param none
	* @return  group id rows : array of group ids, if successful
	*          false : if not successful
	* @author  Anirudh Subramanian
	*/
	public function getAllGroups()
	{		
		$sql = "SELECT * FROM ".TABLE_PREFIX."error_type_groups gg, ".TABLE_PREFIX."language_text l
                 WHERE gg.name is not NULL
                   AND gg.name = l.term
                   AND l.language_code = '".$_SESSION['lang']."'
                 ORDER BY gg.group_id";

		return Utility::sortArrayByNumInField($this->execute($sql), 'group_id');
	}	  


}
