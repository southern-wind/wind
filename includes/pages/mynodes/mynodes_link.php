<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005 Nikolaos Nikalexis <winner@cube.gr>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 dated June, 1991.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

class mynodes_link {

	var $tpl;
	
	function mynodes_link() {
		
	}
	
	function form_link() {
		global $db, $vars;
		$form_link = new form(array('FORM_NAME' => 'form_link'));
		$form_link->db_data('links.type, links.peer_node_id, links.peer_ap_id, links.protocol, links.ssid, links.channel, links.status, links.equipment, links.info');
		$form_link->db_data_values("links", "id", get('link'));
		$form_link->db_data_enum('links.peer_node_id', $db->get("id AS value, name AS output", "nodes"));
		$form_link->db_data_enum('links.peer_ap_id', $db->get("id AS value, ssid AS output", "links", "type = 'ap'"));
		return $form_link;
	}
	
	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['link_method'] = (get('link') == 'add' ? 'add' : 'edit' );
		$this->tpl['form_link'] = $construct->form($this->form_link(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_form_link() {
		global $main, $db;
		$form_link = $this->form_link();
		$link = get('link');
		$ret = TRUE;
		$f = array("date_in" => date_now(), "node_id" => get('node'));
		switch ($_POST['links__type']) {
			case 'p2p':
				$f['peer_ap_id'] = '';
				$f['peer_node_id'] = $_POST['links__peer_node_id'];
				break;
			case 'client':
				$f['peer_ap_id'] = $_POST['links__peer_ap_id'];
				$f['peer_node_id'] = '';
				break;
		}
		$ret = $form_link->db_set($f,
								"links", "id", $link);
		
		if ($ret) {
			$main->message->set_fromlang('info', 'insert_success', makelink("", TRUE));
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>