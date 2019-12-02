<img src="<?=$siteRoot?>images/buttons/search.png" style="vertical-align:text-bottom"> <a href="javascript:;" onclick="Util.Toggle('filters')">Filter Table</a>
<?php if($filteron) { ?>
| <a href="<?=$baseurl?>/<?=$page?>/<?=$sort?>/<?=$order?>?event=clearFilter">Clear Filter</a>
<?php } ?>
<div style="position:relative">
<div id="filters" style="position:absolute;top:0;right:0;background:#FFE0E0;border:1px solid #FF7676;padding:4px;display:none;width:250px;">
<form>
<input type="hidden" name="event" value="setfilter">
<table>
<tr>
	<td style="width:100px">Search</td>
	<td><input type="text" name="<?=$prefix?>filter" style="width:150px" value="<?=$filter?>"></td>
</tr>
<tr>
	<td>Class</td>
	<td>
		<select name="<?=$prefix?>filterclass" style="width:154px">
			<option value="all" <?=($filterclass=="all"?"selected":"")?>>All</option>
			<?php foreach($classfilters as $key => $temp) { ?>
			<option value="<?=$key?>" <?=($filterclass==$key?"selected":"")?>><?=$key?></option>
			<?php } ?>
		</select>
	</td>
</tr>
<tr>
	<td>Min DKP</td>
	<td><input type="text" name="<?=$prefix?>mindkp" style="width:150px" value="<?=$mindkp?>"></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="Search" class="mediumButton"></td>
</tr>
</table>
</form>
</div></div>

