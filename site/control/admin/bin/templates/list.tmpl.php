<div class="controlPanelListBorder">
	<div class="borderHeader">

		<div class="borderHeaderInner" onclick="document.location='<?=$SiteRoot?>ControlPanel'"><img src="<?=$directory?>images/list/controlpanel.gif" >Control Panel</div>
	</div>
	<div class="borderContent">

		<div id="controlPanelList">
			<ul id="navlist">
				<li><a>Settings</a>
					<ul>
						<li><a href="<?=$SiteRoot?>ControlPanel/Webpages" >Webpages</a></li>
						<li><a href="#">Configuration</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/Themes">Themes</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/Library">Part Library</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/Templates">Templates</a></li>
					</ul>
				</li>
				<li><a>Security</a>
					<ul>
						<li><a href="<?=$SiteRoot?>ControlPanel/Users">Users</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/UserGroups">Usergroups</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/Permissions">Permissions</a></li>
						<li><a href="<?=$SiteRoot?>ControlPanel/Database">Database Functions</a></li>
					</ul>
				</li>
				<?php foreach($categories as $category) { ?>
				<li><a><?=$category->name?></a>
					<ul>
					<?php foreach($category->items as $item){ ?>
						<?php if($item->type == controlPanelItem::TYPE_SUBCATEGORY) { ?>
						<li>
							<a><?=$item->name?></a>
							<ul>
								<?php foreach($item->items as $subitem){ ?>
								<li><a href="<?=$subitem->link?>"><?=$subitem->name?></a></li>
								<?php } ?>
							</ul>
						</li>
						<?php } else { ?>
						<li><a href="<?=$item->link?>"><?=$item->name?></a></li>
						<?php } ?>
					<?php } ?>
					</ul>
				</li>
				<?php } ?>
			</ul>
		</div>
	<span style="clear:both"></span>
	</div>
</div>