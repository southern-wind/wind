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
		'default' => '0'));
$tb->addColumn('hostname', 'varchar(50)', array(
                'not_null' => true));
$tb->addColumn('cname', 'varchar(50)', array(
                'not_null' => true));
$tb->addColumn('info', 'text');

/*******************************************************
 *         Change IP columns to unsigned ints         */


// areas

$update->modifyColumn('areas', 'ip_start', 'ip_start', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));
$update->modifyColumn('areas', 'ip_end', 'ip_end', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));

// dns_nameservers

$update->modifyColumn('dns_nameservers', 'ip', 'ip', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));

// ip_addresses

$update->modifyColumn('ip_addresses', 'ip', 'ip', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));
// ip_ranges

$update->modifyColumn('ip_ranges', 'ip_start', 'ip_start', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));
$update->modifyColumn('ip_ranges', 'ip_end', 'ip_end', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));

// regions

$update->modifyColumn('regions', 'ip_start', 'ip_start', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));
$update->modifyColumn('regions', 'ip_end', 'ip_end', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));

// subnets

$update->modifyColumn('subnets', 'ip_start', 'ip_start', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));
$update->modifyColumn('subnets', 'ip_end', 'ip_end', 'int unsigned', array(
                'not_null' => true,
                'default' => '0'));

return $update;

