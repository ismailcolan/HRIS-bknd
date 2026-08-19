<?php
/** The CompanyOffice view */

class BDDView extends AppView {

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function List($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], "fcName", "ASC", self::ADMIN_LISTS_ROWS_LIMIT);

        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Important")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "ResCount")], $c);
		//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Added")], $c);
		//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Updated")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));

        table::execute($query);
        list($delete) = self::AllLnksCode($pg, array("delete"));

        foreach (table::$data as $r => &$cells) {  

			  if ($cells[$pg["pfx"] . "Updated"] == '0000-00-00 00:00:00') 
				  $updated='';
			  else $updated = val::convDate(MYSQL_DATE,LOCAL_PHP_DATETIME_LONG,$cells[$pg["pfx"] . "Updated"]);
			  //date(LOCAL_PHP_DATETIME_LONG,strtotime($cells[$pg["pfx"] . "Updated"]));<<<
			  
			  
            $cells[$pg["pfx"] . "Name"] = 
			form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
			

            if($cells[$pg["pfx"] . "Important"] === "Yes") {
                $cells[$pg["pfx"] . "Name"] = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>'.$cells[$pg["pfx"] . "Name"].'</strong>';
            } 
            if($cells[$pg["pfx"] . "ResCount"] == 0) {
                $cells[$pg["pfx"] . "active"] = sprintf($delete, $cells[$pg["key"]]);
            } else {
                $cells[$pg["pfx"] . "active"] = form::linkOut($pg["details"]["url"] . "?cmd=alert&id=" . $cells[$pg["key"]], '<span class="fa fa-trash-o"></span>', ["class" => "tbl-ico"]);              
            }
			 
        }
        // <a href="rcategory-details?id=3&amp;cmd=trash" onclick="return confirm('Do you really want to delete this category and connected data?');" class="tbl-ico" title="Delete category"></a>
        table::load();
    }

    
}