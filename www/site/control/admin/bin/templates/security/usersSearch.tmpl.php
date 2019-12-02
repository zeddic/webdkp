<form  action="<?=$PHP_SELFDIR?>users" method="post" name="usersearch" style="display:inline">

<input type="text" style="width:210px" id="user_search_input" name="users_search" onkeypress="Util.SubmitIfEnter('usersearch',event)" value="<?=$search?>">
<br />
<input type="submit" value="Search">
<br />
<?php if($search){ ?>
<b><?=$hits?></b> match<?=($hits!=1?"es":"")?>... <br />
<a href="<?=$PHP_SELFDIR?>users?v<?=$iid?>=users&users_clearsearch=1">Clear Search</a>
<?php } ?>
</form>
