<?php

if ($domains_processed == 1) {

			$sql = "update v_dialplan_details ";
      $sql .= "set dialplan_detail_data = replace(dialplan_detail_data, '-','@') ";
 			$sql .= "where dialplan_detail_type = 'conference' and dialplan_detail_data like '%-%';";
			//echo $sql."\n";
			$db->exec($sql);
			unset($sql);

}

?>
