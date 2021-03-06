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
{include assign=panoramic file="generic/photosview_image.tpl" image=$photosview.PANORAMIC}
<table border="0" cellspacing="0" align="center">
	<tr>
	<td colspan="3"><br/></td>
	</tr>
	<tr>
	<td class="node-view-left-top" >{include file="generic/photosview_image.tpl" image=$photosview.NW}</td>
	<td class="node-view-left-top" >{include file="generic/photosview_image.tpl" image=$photosview.N}</td>
	<td class="node-view-right-top" >{include file="generic/photosview_image.tpl" image=$photosview.NE}</td>
	</tr>
	<tr>
	<td class="node-view-left-mid" >{include file="generic/photosview_image.tpl" image=$photosview.W}</td>
	<td class="node-view-left-mid" ><img src="{$img_dir}/compass.png" alt="" /></td>
	<td class="node-view-right-mid" >{include file="generic/photosview_image.tpl" image=$photosview.E}</td>
	</tr>
	<tr>
	<td class="node-view-left-bottom" >{include file="generic/photosview_image.tpl" image=$photosview.SW}</td>
	<td class="node-view-left-bottom" >{include file="generic/photosview_image.tpl" image=$photosview.S}</td>
	<td class="node-view-right-bottom" >{include file="generic/photosview_image.tpl" image=$photosview.SE}</td>
	</tr>
	<tr>
	<td colspan="3" align="center">{assign var=t value="photos__view_point-PANORAMIC"}{$panoramic}</td>
	</tr>
</table>