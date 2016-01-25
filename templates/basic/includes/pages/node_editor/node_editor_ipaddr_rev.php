<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2014      by WiND Contributors (see AUTHORS.txt)
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

class node_editor_ipaddr_rev {

        var $tpl;

        function __construct() {

        }

        function form_ipaddr_rev() {
                global $db, $vars;
                $form_ipaddr_rev = new form(array('FORM_NAME' => 'form_ipaddr_rev'));
                $form_ipaddr_rev->db_data('ip_addresses.ip, ip_addresses.hostname,ip_addresses.mac, ip_addresses.type, ip_addresses.always_on, ip_addresses.info,ip_addresses.zone_type');
                $form_ipaddr_rev->db_data_values("ip_addresses", "id", get('ipaddr_rev'));
                if (get('ipaddr_rev') != 'add') {
                        $form_ipaddr_rev->data[0]['value'] = long2ip($form_ipaddr_rev->data[0]['value']);
                }
                //Set default as reverse
                $form_ipaddr_rev->data[6]['value'] = 'reverse';
                return $form_ipaddr_rev;
        }

        function output() {
                if ($_SERVER['REQUEST_METHOD'] == 'POST'
                                && method_exists($this, 'output_onpost_'.$_POST['form_name'])) {
                        return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
                }
                global $construct;
                $this->tpl['ip_address_method'] = (get('ipaddr_rev') == 'add' ? 'add' : 'edit' );
                $this->tpl['form_ipaddr_rev'] = $construct->form($this->form_ipaddr_rev(), __FILE__);
                return template($this->tpl, __FILE__);
        }

        function output_onpost_form_ipaddr_rev() {
                global $construct, $main, $db;
                $form_ipaddr_rev = $this->form_ipaddr_rev();
                $ipaddr_rev = get('ipaddr_rev');
                $ret = TRUE;
                $_POST['ip_addresses__ip'] = ip2long($_POST['ip_addresses__ip']);
                $ret = $form_ipaddr_rev->db_set(array('node_id' => intval(get('node'))),
                                "ip_addresses", "id", $ipaddr_rev);

                if ($ret) {
                        $main->message->set_fromlang('info', 'insert_success',
                                        make_ref('/node_editor', array("node" => get('node'))));
                } else {
                        $main->message->set_fromlang('error', 'generic');
                }
        }

}

?>
