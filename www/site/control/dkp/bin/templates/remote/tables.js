<?php foreach($dkptables as $table) { ?>
WebDKP.AddTable(<?=(util::json($table,true))?>);
<?php } ?>
WebDKP.SetupBasic();