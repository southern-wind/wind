<?php
/*
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
$update = new DBUpdateDescriptor(new SchemaVersion(1,2), new SchemaVersion(1,3));
/******************************************
 *         ip_addresses database changes          */

// column for zone_types
$update->newColumn('ip_addresses', 'zone_type', 'zone_type', 'enum('forward', 'reverse', 'fwdNrev')', array(
                'default' => 'fwdNrev'));

$update->modifyColumn('ip_cname', 'node_id', 'node_id', 'int unsigned', array(
		'key' => true,
		'not_null' => true,
		'default' => '0'));
return $update;
