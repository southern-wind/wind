{*
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
 *}
<table border="0" cellspacing="0" cellpadding="0" class="table-d1">
<tr>
<td rowspan="2" class="table-d1-side">&nbsp;</td>
<td class="table-d1-title-text" >{assign var=t value="links__type-"|cat:$data.1.links__type}{$lang.db.$t} [SSID: {$data.1.links__ssid|escape} ]</td>
<td rowspan="2" class="table-d1-side2">&nbsp;</td>
</tr>
<tr>
<td class="table-d1-text1">
		<table border="0" cellpadding="0" cellspacing="6" class="table-form">
		<tr class="table-form-row1">
		<td class="table-node-link-info"><img src="{$img_dir}/node.gif" width="32" height="32" alt="{$lang.db.peer}" /></td>
		<td class="table-node-link-info">

<table class="table">
        <thead>
        <tr>
        <th class="table-node-key2">{$lang.db.links__type}</th>
        <th class="table-node-key2">{$lang.db.links__status}</th>
        <th class="table-node-key2">{$lang.db.links__date_in}</th>
        <th class="table-node-key2">{$lang.db.links__due_date}</th>
        <th class="table-node-key2">{$lang.db.links__protocol}</th>
        <th class="table-node-key2">{$lang.db.links__ssid}</th>
        <th class="table-node-key2">{$lang.db.links__channel}</th>
        <th class="table-node-key2">{$lang.db.links__frequency}</th>
        <th class="table-node-key2">{$lang.db.links__equipment}</th>
        </tr>
        </thead>
        <tr>
<tr>
        <td class="table-node-value2">{assign var=t value="links__type-"|cat:$data.1.links__type}{$lang.db.$t}</td>
        <td class="{if $data.1.links__status == 'active'}link-up{else}link-down{/if}">{assign var=t value="links__status-"|cat:$data.1.links__status}{$lang.db.$t}</td>
        <td class="table-node-value2">{$data.1.links__date_in|date_format:"%x"}</td>
        <td class="table-node-value2">{$data[rowl].links__due_date|date_format:"%x"}</td>
        <td class="table-node-value2">{$data.1.links__protocol|escape}</td>
        <td class="table-node-value2">{$data.1.links__ssid|escape}</td>
        <td class="table-node-value2">{$data.1.links__channel|escape}</td>
        <td class="table-node-value2">{$data[rowl].links__frequency|escape}</td>
        <td class="table-node-value2">{$data.1.links__equipment|escape|nl2br}</td>
</tr></table>

                </td>
                </tr>
                <tr><td></td>
                <td class="table-node-link-info">
                {include file="generic/section-level5.tpl" title="`$lang.db.links__info`" content="`$data.1.links__info`"|escape|nl2br}
                </td></tr>
                <tr><td></td>
                <td class="table-node-link_info">
                <div class="section section-level-5 panel panel-default">
                <div class="panel-heading">
                <h5 class="panel-title">{$lang.clients} </h5></div>
                <div class="content panel-body">

                <table class="table">
                <thead>
                <tr>
                <th class="table-node-key2">{$lang.node}</th>
                <th class="table-node-key2">{$lang.db.links__status}</th>
                <th class="table-node-key2">{$lang.distance}</th></tr>

                </tr>
                {if $data.1.c_node_id != ''}
                        {section name=c loop=$data start=1}
                                <tr><td class="table-node-value2">
                                <a href="{$extra_data.EDIT[c]}">{$data[c].c_node_name|escape} (#{$data[c].c_node_id})</a></td>
                                <td class="{if $data[c].c_status == 'active'}link-up{else}link-down{/if}">
                                {assign var=t value="links__status-"|cat:$data[c].c_status}{$lang.db.$t}</td>
                                <td class="table-node-value2">
                           	{ * round 2 decimal places * }
                                {$data[c].distance|number_format:2}</td>
                                </tr>
                        {/section}
                {/if}
                </table>
                </div></div>
                </td>
                </tr>
                </table>
</td>
</tr>
</table>
