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


$update = new DBUpdateDescriptor(new SchemaVersion(1,1), new SchemaVersion(1,2));

/******************************************
 *         CNAME database changes          */

// TABLE ip_cname
$tb = $update->newTable('ip_cname');
$tb->addColumn('id', 'int unsigned', array(
		'not_null' => true,
		'ai' => true,
		'pk' => true));
$tb->addColumn('date_in', 'datetime', array(
		'not_null' => true,
		'default' => "'0000-00-00 00:00:00'"));
$tb->addColumn('node_id', 'int unsigned', array(
		'not_null' => true,
		'default' => '0',
		'unique' => true));
$tb->addColumn('hostname', 'varchar(50)', array(
                'not_null' => true,
                'default' => ''));
$tb->addColumn('cname', 'varchar(50)', array(
                'not_null' => true,
                'default' => ''));
$tb->addColumn('info', 'text');

return $update;
