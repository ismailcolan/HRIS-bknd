<?php

/** The webapp admin view class */

class WebappSuperAdminView extends AppView {



    /** Shows the controller list table - allowing the type filtering

     * @param array $pg <- controller::$pg 

     * @param string $query <- model::query($cond)

     * @param array $lists <- ["roles"=>[...]]*/

    final static function All($pg, $query, $lists){

        table::create("admins", "waaRegDate", "desc");

            table::$cols[] = Array("ID", "waaID", array("width"=>"50px"));

            table::$cols[] = Array("Username", "waaUsername");

            table::$cols[] = Array("Role", "waaRole");

            table::$cols[] = Array("Email", "waaEmail");

            table::$cols[] = Array("Date", "waaRegDateFormatted", array("sort"=>"waaRegDate","width"=>"140px"));

            table::$cols[] = Array("...", "lnks", array("width"=>"100px","sort"=>false));

            table::execute($query);

            $delConfirm = str_replace("row", strtolower($pg["item"]), TXT_DELETE_CONFIRM);

            foreach(table::$data as $r => &$cells){

                $lnks = array();

                if($cells[$pg["key"]]!=1 && $cells[$pg["key"]]!==AppModel::$admin[WebappAdminModel::$dt["key"]]) {

                    $lnks[] = cf::getOutput("form", "link", array($pg["details"]["url"]."?cmd=delete&id=".$cells[$pg["key"]] ,"<span class=\"fa fa-trash-o\"></span>",array("onclick"=>"return confirm('".$delConfirm."');","class"=>"tbl-ico left", "title"=>"Delete ".$pg["item"])));

                }

                $lnks[] = cf::getOutput("form", "link", array($pg["password"]["url"]."?id=".$cells[$pg["key"]] ,"<span class=\"fa fa-key\"></span>",array("class"=>"tbl-ico", "title"=>"Change password")));

                $lnks[] = cf::getOutput("form", "link", array($pg["details"]["url"]."?id=".$cells[$pg["key"]] ,"<span class=\"fa fa-pencil-square-o\"></span>",array("class"=>"tbl-ico", "title"=>$pg["item"]." Details")));

                $cells["lnks"] = implode("",$lnks);

                $cells[$pg["key"]] = $cells[$pg["key"]]==3 ? array($cells[$pg["key"]] ,array("style"=>"color:red")) : $cells[$pg["key"]];

            }

        table::load();

        //self::ShowAdminRolesRights($lists["roles"]);

    }

    private static function ShowAdminRolesRights($roles){

        form::openDiv(array("id"=>"admin-roles-rights-container"));

            form::openTag("table", array("id"=>"admin-roles-rights","class"=>"table"));

                form::tag("<h6 class=\"text-muted\"><i class=\"fa fa-users\" ></i> Rights of the admin user roles</h6>","caption");

                $cols = array("role"=>"fa-user", "create"=>"fa-plus", "read"=>"fa-folder-o", "update"=>"fa-pencil-square-o", "delete"=>"fa-trash-o", "admins"=>"fa-user-plus", "changelog"=>"fa-history");

                form::openTag("thead");

                    form::openTag("tr");

                    foreach($cols as $k=>$c){

                        form::tag("<i class=\"fa ".$c."\" ></i>","th", ($c==="fa-user"?array("title"=> ucfirst($k)):array("width"=>"40px;", "title"=> ucfirst($k))));

                    } 

                    form::closeTag("tr");

                form::closeTag("thead");

                form::openTag("tbody");

                foreach($roles as $role){

                    form::openTag("tr");

                    $rights = in_array($role, AccessModel::$AdminRolesWithEditEnabled) ? array("create","read","update","delete") : array("read");

                    if(in_array($role, array("Admin","Editor"))){

                        $rights[] = "changelog";

                        $rights[] = "admins";

                    }

                    foreach(array_keys($cols) as $c){

                        if($c==="role"){    $innerHtml = $role;

                        } else {            $innerHtml = "<i class=\"fa ".(in_array($c, $rights)?"fa-check text-success":"fa-ban text-danger")."\" ></i>";}

                        form::tag($innerHtml,"td");

                    }

                    form::closeTag("tr");

                }

                form::closeTag("tbody");

            form::closeTag("table");

        form::closeDiv();

    }





    /** Shows a controller details view

     * @param type $data <- controller::$data

     * @param type $listUrl <- controller list page URL */

    final static function Details($data, $pg, $list){

        //WebpageAssetsAdminView::pageTitle($title);

        $fldArgsBase = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-3 col-md-2", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control");

        //form::openDiv(array("class"=>"container"));

            form::codeStyle(true, 1, false);

            form::openTag("div",array("class"=>"form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));

                form::openTag("div",array("class"=>"row"));

                    form::openTag("div",array("class"=>"col-sm-8 col-sm-offset-2"));

                    form::$ofsetIndentsCount = 2;

                    form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));

                    form::inpField(

                        "Username:",

                        "waaUsername",

                        $data["waaUsername"],

                        $fldArgsBase

                    );

                    form::inpField(

                        "Email:",

                        "waaEmail",

                        $data["waaEmail"],

                        (array("type"=>"email")+$fldArgsBase)

                    );

                    form::inpSelect(

                        "Role:",

                        "waaRole",

                        $list["roles"],

                        $data["waaRole"],

                        $fldArgsBase

                    );

                    if(!is_array($data) || !array_key_exists(WebappAdminModel::$dt["key"],$data)){

                        self::ChangePassword($pg, false);

                    }

                    form::inpSaveCancel(ADMIN.$pg["list"]["url"],array("fld-class"=>"form-group", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default"));

                    form::closeTag("form");

                    form::codeStyle(true, 1, true);

                    form::closeTag("div",array("class"=>"col-sm-10 col-sm-offset-1"));

                form::closeTag("div",array("class"=>"row"));

            form::closeTag("div",array("class"=>"form-panel"));

        //form::closeDiv(array("class"=>"container"));

    }

    

    static function ChangePassword($pg, $oldPass = true){

        //WebpageAssetsAdminView::pageTitle($title);

        //$fldArgsBase = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-3 col-md-2", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control");

        //form::openDiv(array("class"=>"container"));

        if(isset(WebappAdminController::$id)/*$oldPass===true*/){

            form::codeStyle(true, 1, false);

            form::openTag("div",array("class"=>"form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));

                form::openTag("div",array("class"=>"row"));

                    form::openTag("div",array("class"=>"col-sm-8 col-sm-offset-2"));

                    form::$ofsetIndentsCount = 2;

                    form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));

                        $arg = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4 col-md-3", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control", "type"=>"password");

                        //$arg = array("fld-class"=>"width-50pc","type"=>"password");

                        if($oldPass===true){

                            form::inpField(

                                "Old password:",

                                "OldPass",

                                "",

                                $arg

                            );

                        }

        } else {

            $arg = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-3 col-md-2", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control", "type"=>"password");

        }

            form::inpField(

                "Password:",

                "waaPass",

                "",

                $arg

            );

            form::inpField(

                "Re password:",

                "waaPassRe",

                "",

                $arg

            );

        if(isset(WebappAdminController::$id)/*$oldPass===true*/){

                    form::$ofsetIndentsCount = 2;

                    $url = array_key_exists("list",$pg) ? $pg["list"]["url"] : ADMIN."home";

                    form::inpSaveCancel($url,array("fld-class"=>"form-group", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default"));

                    form::closeTag("form");

                    form::codeStyle(true, 1, true);

                    form::closeTag("div",array("class"=>"col-sm-10 col-sm-offset-1"));

                form::closeTag("div",array("class"=>"row"));

            form::closeTag("div",array("class"=>"form-panel"));

        //form::closeDiv(array("class"=>"container"));

        }

    }

    

    static function Login($goto){ 

	    

        form::openTag("section");

			form::openDiv(array("class"=>"container"));

				

				form::openDiv(array("class"=>"row login-holder"));

					msg::get();

					form::openDiv(array("class"=>"col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3"));

						form::openDiv(array("class"=>"row"));

							form::openDiv(array("class"=>"col-sm-12"));

								form::openTag("h3");

									form::link(WP, 'SuperAdmin Login');

								form::closeTag("h3");

							form::closeDiv();

							form::openDiv(array("class"=>"col-sm-12 login-box"));

								form::openDiv(array("class"=>"login-inner"));

									$check = cf::hashTokenByPassAndSalt($_SESSION["login-src-check"], "login-page-check");

									form::openTag("form",array("validate"=>true, "action"=>"?q=".$check, "class"=>"form-horizontal"));

                                        $user = filter_has_var(INPUT_POST, "user") ? filter_input(INPUT_POST, "user", FILTER_SANITIZE_SPECIAL_CHARS) : null;

										form::inpField("Username: ","user", $user, array("autofocus"=>true, "id"=>"inputUser", "fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4", "wrap-class"=>"col-sm-8", "class"=>"form-control"));

										form::inpField("Password: ","pass","",array("type"=>"password", "id"=>"inputPass", "fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4", "wrap-class"=>"col-sm-8", "class"=>"form-control"));

										form::inpSaveCancel(null,array("fld-class"=>"form-group", "wrap-class"=>"col-sm-8 col-sm-offset-4", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default"),"Sign in");

//										form::inpInfo(" ", form::linkOut(WP."login-recover", "forgotten password?"), array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4", "wrap-class"=>"col-sm-8"));



                                        if(!is_null($goto)){

                                            form::inpField(null, "goto", $goto, array("type"=>"hidden"));

                                        }

                                        form::tag(null,"input",array("type"=>"hidden", "id"=>"sr", "name"=>"resolution", "value"=>"")); form::tag('document.getElementById("sr").value=screen.width+" "+screen.height;',"script");

									form::closeTag("form");

								form::closeDiv();

							form::closeDiv();

						form::closeDiv();//row

					form::closeDiv();//col-sm

				form::closeDiv(); //row login-holder

			form::closeDiv(); //container

        form::closeTag("section");

    }

}