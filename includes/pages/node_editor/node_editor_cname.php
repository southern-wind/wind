<?php
/*x
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2014 	by WiND Contributors (see AUTHORS.txt)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class node_editor_cname {

	var $tpl;
	
	function __construct() {
		
	}
	
	function form_cname() {
		global $db, $vars;
		$form_cname = new form(array('FORM_NAME' => 'form_cname'));
		$form_cname->db_data('ip_cname.hostname, ip_cname.cname, ip_cname.info');
		$form_cname->db_data_values("ip_cname", "id", get('cname'));
		return $form_cname;
	}
	
	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['ip_cname_method'] = (get('cname') == 'add' ? 'add' : 'edit' );
		$this->tpl['form_cname'] = $construct->form($this->form_cname(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_form_cname() {
		global $construct, $main, $db;
		$form_cname = $this->form_cname();
		$cname = get('cname');
		$ret = TRUE;
		$_POST['ip_cname__cname'] = validate_hostname($_POST['ip_cname__cname']);
		$_POST['ip_cname__hostname'] = validate_hostname($_POST['ip_cname__hostname']);
		$ret = $form_cname->db_set(array('node_id' => intval(get('node'))),
								"ip_cname", "id", $cname);
		
		if ($ret) {
			$main->message->set_fromlang('info', 'insert_success', make_ref('/node_editor', array("node" => get('node'))));
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>
