<?php
// vim: set expandtab shiftwidth=4 softtabstop=4 tabstop=4:
	if(!isset($_PJ_root)) {
		print "<b>FEHLER:</b> \$_PJ_root ist <b>nicht festgelegt</b>! (" . __FILE__ . ", Zeile: " . __LINE__ . ")";
		exit;
	}

	class Listing {
		/**
		* Attributes array
		* 
		* Stores attributes 
		* 
		* @var		array
		* @access	private
		* @see		set()
		*/
		var $__attributes	= array();

		/**
		* Set attributes.
		*
		* Set attributes of the list object.
		*
		* @access	public
		* @param	string	name of the attribute
		* @param	string	value to be stored in attributes
		* @param	boolean	should existing values be overwritten?
		* @return	boolean
		* @see		$__attributes, get()
		*
		*/
		function set($_name, $_value, $_overwrite = false) {
			if(($this->__attributes[$_name] == NULL) || $_overwrite) {
				$this->__attributes[$_name] = $_value;
				return true;
			} else {
				return false;
			}
		} /* End of function List::set() */

		/**
		* Get attributes
		*
		* Returns attributes set by function set().
		*
		* @access	public
		* @param	string	name of the attribute
		* @return	mixed
		*/
		function get($_name) {
			/* discover attribute to get and return the value */
			if(($this->__attributes[$_name] != NULL)) {
				return $this->__attributes[$_name];
			} else {
				return false;
			}
		} /* End of function List::get() */

	}
?>