<?php

class WebappAdminSettingsView extends AppView {
    
    private static  $dtKey2FldType  = array( 
                                            "ORDER_SUBMISSION_NOTIFY_EMAILS"=>"textarea",
                                        ),
                    $dtKey2FldLabel = array( 
                                            "ORDER_SUBMISSION_NOTIFY_EMAILS"=>"When an order is placed, notify the following emails",
                                        ),
                    $dtKey2FldNote = array( 
                                            "ORDER_SUBMISSION_NOTIFY_EMAILS"=>"Please divide the emails into new rows.",
                                        );


    static function Settings($data, $pg){
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
            form::openTag("div",array("class"=>"row"));
                form::openTag("div",array("class"=>"col-sm-10 col-sm-offset-1"));
                form::$ofsetIndentsCount = 2;
                form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));
                    $arg = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4 col-md-3", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control", "type"=>"password");
                    foreach($data as $key=>$value){
                        form::inpField(
                            self::$dtKey2FldLabel[$key].":",
                            $key,
                            self::fldDataFormat($key, $value),
                            (array("type"=>self::$dtKey2FldType[$key])+$arg)
                        );
                        if(array_key_exists($key, self::$dtKey2FldNote)){
                            $argsNote = (array("type"=>"p", "fld-class"=>$arg["fld-class"]." settings-fld-note", "wrap-class"=>$arg["wrap-class"]." col-sm-offset-4 col-md-offset-3", "class"=>"form-control-static")+$arg);
                            form::inpField(null,null,self::$dtKey2FldNote[$key],$argsNote);
                        }
                    }
                    form::tag(null, "input", array("type"=>"hidden","name"=>"settings-form", "value"=>"1"));
                form::$ofsetIndentsCount = 2;
                form::inpSaveCancel(ADMIN."home" ,array("fld-class"=>"form-group", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default")/*, "Save", "&laquo; Back to Dashboard"*/);
                form::closeTag("form");
                form::codeStyle(true, 1, true);
                form::closeTag("div",array("class"=>"col-sm-10 col-sm-offset-1"));
            form::closeTag("div",array("class"=>"row"));
        form::closeTag("div",array("class"=>"form-panel"));
    }
    private static function fldDataFormat($key, $value){
        switch($key){
            case "ORDER_SUBMISSION_NOTIFY_EMAILS":
                $valueFormatted = str_replace(",","\r\n", $value);
                break;
            default: $valueFormatted = $value;
        }
        return $valueFormatted;
    }

}