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
<td class="table-d1-title-text" ><a href="{$extra_data.EDIT[rowl]}">{$data[rowl].peer_node_name|escape} (#{$data[rowl].links__peer_node_id})</a></td>
<td rowspan="2" class="table-d1-side2">&nbsp;</td>
</tr>
<tr>
<td class="table-d1-text1">
                <table border="0" cellpadding="0" cellspacing="6" class="table">
                <tr class="table-form-row1">
                <td class="table-node-link-info"><img src="{$img_dir}/node.gif" width="32" height="32" alt="{$lang.db.peer}" /></td>
                <td class="table-node-link-info">

<table class="table">
        <thead>
        <tr>
        <th class="table-node-key2">Link type</th>
        <th class="table-node-key2">Created</th>
        <th class="table-node-key2">Due Date</th>
        <th class="table-node-key2">SSID</th>
        <th class="table-node-key2">Protocol</th>
        <th class="table-node-key2">Channel</th>
        <th class="table-node-key2">Frequency (Mhz)</th>
        <th class="table-node-key2">Equipment</th>
        <th class="table-node-key2">Status</th>
        </tr>
        </thead>
        <tr>
<tr>
        <td class="table-node-value2">{assign var=t value="links__type-"|cat:$data[rowl].links__type}{$lang.db.$t}</td>
        <td class="table-node-value2">{$data[rowl].links__date_in|date_format:"%x"}</td>
        <td class="table-node-value2">{$data[rowl].links__due_date|date_format:"%x"}</td>
        <td class="table-node-value2">{$data[rowl].links__ssid|escape}</td>
        <td class="table-node-value2">{$data[rowl].links__protocol|escape}</td>
        <td class="table-node-value2">{$data[rowl].links__channel|escape}</td>
        <td class="table-node-value2">{$data[rowl].links__frequency|escape}</td>
        <td class="table-node-value2">{$data[rowl].links__equipment|escape|nl2br}</td>
        <td class="{if $data[rowl].links__status == 'active'}link-up{else}link-down{/if}">{assign var=t value="links__status-"|cat:$data[rowl].links__status}{$lang.db.$t}</td>
</tr>
</table>


                <div>
                {include file="generic/section-level5.tpl" title="`$lang.db.links__info`" content="`$data[rowl].links__info`"|escape|nl2br}
                {include file="generic/plot.tpl"}
                </div>
                </td></tr>
                </table>
</td>
</tr>
</table>
