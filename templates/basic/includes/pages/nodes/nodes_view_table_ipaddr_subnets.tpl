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
<table class="table-form">
{section name=row loop=$data start=1}
{if $data[row].ip_start != $cur}
	{$close2}{$close1}
	{assign var=close1 value=""}
	{assign var=close2 value=""}
	{assign var=close1 value="</table></td></tr>"}
	{assign var=cur value=$data[row].ip_start}
	<tr><td>
	<table class="table-form">
	<tr><td class="table-search-menu-text">
		<img src="{$img_dir}/admin.gif" alt="{$lang.db.subnet}" />
		{assign var=t1 value="subnets__type-"|cat:$data[row].type}
		{assign var=t2 value="links__type-"|cat:$data[row].links__type}
		{$lang.db.$t1}
		{$lang.db.$t2}
		{if $data[row].nodes__name != ''}[{$data[row].nodes__name|escape} (#{$data[row].nodes__id})] {/if}
		({$data[row].ip_start} - {$data[row].ip_end})
	</td></tr>
	{if $data[row].date_in != ''}
	{assign var=close2 value="</table></td></tr>"}
	<tr><td>
		{include file="constructors/table2.tpl"}
                {/if}
        </tr>
        {/if}
{/section}
{$close2}
</table>
