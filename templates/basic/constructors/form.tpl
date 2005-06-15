{*
 * WiND - Wireless Nodes Database
 * Basic HTML Template
 *
 * Copyright (C) 2005 Konstantinos Papadimitriou <vinilios@cube.gr>
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
 *}
{literal}<script language="JavaScript" type="text/javascript">
	function pickup(list, text, value, mywindow) {
		if (list.multiple) {
			for(x=0; x<(list.length); x++){
				if (list.options[x].value == value) return;
			}
			var opt = new Option(text, value)
			list.options[list.options.length] = opt
			for(x=0; x<(list.length); x++){
				list.options[x].selected = "true"
			}
		} else {
			if (list.options[0].value == '' && list.options.length == 1) {
				num = 1
			} else {
				num = 0
			}
			list.options[num] = null
			var opt = new Option(text, value)
			list.options[num] = opt
			list.selectedIndex = num
			mywindow.close()
		}
	}
	
	function remove_selected(list) {
		for(x=0; x<(list.length); x++){
			if (list.options[x].selected == true) {
				list.options[x] = null
				x--
			}
		}
		for(x=0; x<(list.length); x++){
			list.options[x].selected = "true"
		}
	}
</script>{/literal}
<form name="{$extra_data.FORM_NAME}" method="post" action="?">
<input type="hidden" name="query_string" value="{$hidden_qs}" />
<input type="hidden" name="form_name" value="{$extra_data.FORM_NAME}" />
<table class="table-form">
{section loop=$data name=d}
	{if $smarty.section.d.index is not even}
	<tr class="table-form-row2">
	{else}
	<tr class="table-form-row1">
	{/if}
	{assign var=fullField value=$data[d].fullField}
	{if $data[d].Type == 'caption'}
		<td class="table-form-title" colspan="2">{$data[d].Value}</td>
	{elseif $data[d].Type == 'datetime'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td><td class="table-form-field" >{html_select_date time="`$data[d].value`" prefix="CONDATETIME_`$data[d].fullField`_"} - {html_select_time time="`$data[d].value`" prefix="CONDATETIME_`$data[d].fullField`_"}</td>
	{elseif $data[d].Type == 'text'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td><td class="table-form-field" ><textarea class="fld-form-input" name="{$data[d].fullField}">{$data[d].value}</textarea></td>
	{elseif $data[d].Type == 'enum'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td>
		<td class="table-form-field" >
			<select class="fld-form-input" name="{$data[d].fullField}" class="table-form-field" >
				{if $data[d].Null == 'YES'}<option value=""></option>{/if}
				{section loop=$data[d].Type_Enums name=e}
				<option value="{$data[d].Type_Enums[e].value}"{if $data[d].Type_Enums[e].value == $data[d].value} selected="selected"{/if}>{include file=constructors/form_enum.tpl fullField=$fullField value=$data[d].Type_Enums[e].output}</option>
				{/section}
			</select>
		</td>	
	{elseif $data[d].Type == 'enum_multi'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td>
		<td class="table-form-field" >
			<select class="fld-form-input" name="{$data[d].fullField}[]" size="5" multiple="multiple">
				{section loop=$data[d].Type_Enums name=e}
				{assign var="value" value=$data[d].Type_Enums[e].value}
				<option value="{$data[d].Type_Enums[e].value}"{if $data[d].value.$value == 'YES'} selected="selected"{/if}>{include file=constructors/form_enum.tpl fullField=$fullField value=$data[d].Type_Enums[e].output}</option>
				{/section}
			</select>
		</td>	
	{elseif $data[d].Type == 'enum_radio'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td>
		<td class="table-form-field" >
			{if $data[d].Null == 'YES'}<input type="radio" name="{$data[d].fullField}" value="" /><br />{/if}
			{section loop=$data[d].Type_Enums name=e}
			<input type="radio" name="{$data[d].fullField}" value="{$data[d].Type_Enums[e].value}"{if $data[d].Type_Enums[e].value == $data[d].value} checked="checked"{/if} />{include file=constructors/form_enum.tpl fullField=$fullField value=$data[d].Type_Enums[e].output}<br />
			{/section}
		</td>
	{elseif $data[d].Type == 'pickup'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td>
		<td class="table-form-field" >
			<select class="fld-form-input" name="{$data[d].fullField}" class="table-form-field">
				<option value="{$data[d].Type_Pickup.value}">{$data[d].Type_Pickup.output}</option>
			</select>
			{include file=generic/link.tpl content="`$lang.change`" onclick="javascript: open ('`$data[d].Pickup_url`', 'popup', 'width=500,height=400,toolbar=0,resizable=1,scrollbars=1'); return false;"}
		</td>	
	{elseif $data[d].Type == 'pickup_multi'}
		<td class="table-form-title" >{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td>
		<td class="table-form-field" >
			<select class="fld-form-input" name="{$data[d].fullField}[]" size="5" multiple="multiple">
				{section loop=$data[d].Type_Pickup name=e}
				{assign var="value" value=$data[d].Type_Pickup[e].value}
				<option value="{$data[d].Type_Pickup[e].value}" selected="selected">{include file=constructors/form_enum.tpl fullField=$fullField value=$data[d].Type_Pickup[e].output}</option>
				{/section}
			</select>
			{include file=generic/link.tpl content="`$lang.add`" onclick="javascript: open ('`$data[d].Pickup_url`', 'popup', 'width=500,height=400,toolbar=0,resizable=1,scrollbars=1'); return false;"}
			{include file=generic/link.tpl content="`$lang.remove`" onclick="javascript: remove_selected(window.document.`$extra_data.FORM_NAME`.elements['`$data[d].fullField`[]']); return false;"}
		</td>	
	{elseif $data[d].Field|truncate:8:"":true == 'password'}
		<td class="table-form-title">{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td><td class="table-form-field" ><input class="fld-form-input" name="{$data[d].fullField}" type="password" value="{$data[d].value}" /></td>
	{else}
		<td class="table-form-title">{$lang.db.$fullField}{if $data[d].Null != 'YES'}*{/if}:</td><td class="table-form-field" ><input class="fld-form-input" name="{$data[d].fullField}" type="text" value="{$data[d].value}" /></td>
	{/if}
	</tr>
{/section}
<tr><td class="table-form-submit" colspan="2"><input class="fld-form-submit" type="submit" name="submit" value="OK" /></td></tr>
</table>
</form>