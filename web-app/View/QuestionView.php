<?php

/** The Vendor view */
class QuestionView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], $pg["key"]/*"coAdded"*/, "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array("Positions", "Positions");
        table::$cols[] = Array("Answers", "Answers");

        if ($rq === "list") {
//            table::$cols[] = Array("", "answers", array("class" => "link", "sort" => false));
            table::$cols[] = Array("", $pg["key"] . "deactivate", array("class" => "link", "sort" => false));
        }
        if ($rq === "inactive-list") {
            table::$cols[] = Array("", $pg["key"] . "reactivate", array("class" => "link", "sort" => false));
        }

        table::execute($query);
        list($edit, $reactivate, $deactivate, $delete) = self::AllLnksCode($pg, array("edit", "reactivate", "deactivate", "delete"));
        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells[$pg["key"] . "deactivate"] = sprintf($deactivate, $cells[$pg["key"]]);
                $cells[$pg["key"] . "reactivate"] = sprintf($reactivate, $cells[$pg["key"]]);

                $cells["Answers"] = "";
                $ans = AnswerModel::GetQuestionAnswers($cells[$pg["key"]], true);
                $ansHtml = array();
                $char = ord("a");
                foreach($ans as $a) {
                    $hasReject = (!empty($a["rejectedFor"]));
                    $a['aName'] = chr($char) . ") " . $a['aName'];
                    $char++;
                    $ansHtml[] = '<span '.($hasReject ? ' style = "color: red;" ' : '').'>'.$a['aName'].'</span>';
                }
                $cells["Answers"] = implode(",&nbsp;&nbsp;&nbsp;", $ansHtml);

                $name = htmlspecialchars($cells[$pg["pfx"] . "Name"]);
                $cells[$pg["pfx"] . "Name"] = '<a href="/admin/question-details?id=' . $cells[$pg["key"]] . '" title="Edit question">' . $name . '</a>';
                $cells["answers"] = '<a class="tbl-ico" href="/admin/question-answers?id=' . $cells[$pg["key"]] . '" title="Edit question`s answers"><span class="fa fa-archive"></span></a>';

                $cells["Positions"] = implode(",&nbsp;&nbsp;&nbsp;", QuestionPositionsModel::LoadAsKeyValueArray($cells[$pg["key"]]));
            }
        }
        table::load();
    }

    /**Show an model item summary page
     * @param type $dt - controller::$data
     * @param type $pg - controller::$pg
     * @param type $lbls - model::$fldLbl
     */
    final static function Summary($dt, $pg, $lbls)
    {
        msg::get();
        $qp = array_column(QuestionPositionsModel::LoadQuestionPositions($dt[$pg["key"]]), "pName");
        $ans = AnswerModel::GetQuestionAnswers($dt[$pg["key"]], true);
        $ansHtml = ""; $color = "";
        foreach ($ans as $a) {
            if (!empty($a["rejectedFor"])) {
                $aHtml = "<span style='color: red;'>" . $a["aName"] . "</span><br/><small>reject for: <strong>" . implode(", ", array_column($a["rejectedFor"], "pName")) . "</strong></small><br/>";
                $color = ' style="color: red" ';
            } else {
                $aHtml = $a["aName"] . "<br/><small>no rejection</small><br/>";
            }
            $aHtml .= "<br/>";

            $ansHtml .= $aHtml;
        }
        $ansHtml .= '<br/><a class="btn btn-default btn-success" href="'.ADMIN.'question-answers?id='.QuestionController::$id.'">Edit answers <i class="fa fa-pencil"></i> </a>';

        $flds4panel = array();
        if(!empty($color)) {
            $flds4panel[$lbls[($c = $pg["pfx"] . "Name")]] = '<span '.$color.'>'.$dt[$c].'</span>';
        } else {
            $flds4panel[$lbls[($c = $pg["pfx"] . "Name")]] = $dt[$c];
        }
        $flds4panel["Positions"] = implode(", ", $qp);
        $flds4panel["Answers"] = $ansHtml;

        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data <- controller::$data
     * @param type $lbls <- model::$fldLbl
     * @param type $lists <- lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $lists)
    {
        $args = self::$fldArgsBase;
        $allPositions = [];
        if (empty($lists["allpositions"])) {
            $lists["allpositions"] = [];
        }
        foreach ($lists["allpositions"] as $item) {
            $allPositions[$item["pID"]] = htmlspecialchars($item["pName"]);
        }
        $qPositions = [];
        foreach ($lists["positions"] as $item) {
            $qPositions[] = $item["pID"];
        }
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args
        );

        form::inpCheckList(
            "Positions:",
            "positions",
            $allPositions,
            $qPositions,
            $args + array("check-lbl-class" => "check-lbl small")
        );

        form::inpSaveCancel(QuestionController::getBackUrl()["url"], self::$saveCancelArgsBase);

        if (QuestionController::$id) {
            form::tag("", "hr");

            $ansList = AnswerModel::GetQuestionAnswers(QuestionController::$id, true);

            if (count($ansList) === 0) {
                $answers = '<div class="text-danger" style="margin-top: 7px;">No answers yet. This question will be shown only if there are at least 2 answers</div>';
            } else {
                $answers = '';
                foreach ($ansList as $ans) {
                    if ($answers !== "") $answers .= "<hr/>";
                    if (count($ans["rejectedFor"]) === 0) {
                        $rpFor = 'no rejection';
                        $color = "";
                    } else {
                        $rpFor = 'Reject for: ' . implode(', ', array_column($ans["rejectedFor"], "pName"));
                        $color = " style='color: red; '";
                    }
                    $answers .= '<div style="margin-top: 7px;"><strong '.$color.'>' . $ans["aName"] . '; </strong><div>'.$rpFor.'</div></div>';
                }

                if (count($ansList) === 1) {
                    $answers .= '<div class="text-danger"><hr/>Only 1 answer entered. This question will be shown only if there are at least 2 answers</div>';
                }
            }

            //$answers .= '<div style="margin-top: 10px;"><a href="#" class="btn btn-success">Edit answers <i class="fa fa-pencil-square-o"></i></a></div>';


            form::openDiv(["class" => "fld form-group"]);
            form::html('<label class="control-label col-sm-3 col-md-2">Answers:</label>');
            form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);
            form::html($answers);
            form::closeDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);
            form::closeDiv(["class" => "fld form-group"]);

            form::html('<div class="form-group">
                                <br/>
                                <label class="control-label col-sm-3 col-md-2"></label>
                                <div class="col-sm-8 col-md-7 col-lg-5">
                                    <a class="btn btn-default btn-success" href="' . ADMIN . QuestionController::$pg["answers"]["url"] . '?id=' . QuestionController::$id . '">Edit answers <i class="fa fa-pencil"></i> </a>
                                </div>
                           </div>');

        }

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function Answers($data, $pg, $lbls, $lists)
    {
        self::AllAnswers($data, $pg, $lbls, $lists);
    }

    public static function AllAnswers($data, $pg, $lbls, $lists)
    {
        $args = self::$fldArgsBase;
        $qTitle = $data[QuestionModel::$dt["pfx"] . "Name"];
        $qTitle = '<div class="fld form-group"><label class="control-label col-sm-3 col-md-2"></label><div class="col-sm-8 col-md-7 col-lg-5"><h4>' . $qTitle . '</h4></div></div>';
        $qPositions = [];
        foreach ($lists["qPositions"] as $item) {
            $qPositions[$item["pID"]] = $item["pName"];
        }


        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
        form::html($qTitle);


        $answers = $data["answers"];
        foreach ($answers as $key => $answer) {
            $idx = str_replace("ansName", "", $key);
            form::html('<div class="ansBlock">');
            form::tag("", "hr");

            form::html('<div class="form-group">
                                <label class="control-label col-sm-3 col-md-2">Answer:</label>
                                <div class="col-sm-8 col-md-7 col-lg-5">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" name="'.$key.'" value="'.$answer["aName"].'">
                                        </div>
                                        <div class="col-sm-3 no-padding">
                                            <button style="width: 100%;" class="btn btn-default btn-danger" onClick="removeAnswer($(this));">Delete answer</button>
                                        </div>
                                    </div>                                                                       
                                </div>
                   </div>');
            form::inpCheckList(
                "Reject for:",
                "ansPositions" . str_replace("ansName", "", $key),
                $qPositions,
                $answer["arPos"],
                array_merge($args, ["check-lbl-class" => "small"])
            );

            form::html('</div>');
        }


        form::html('<div id="newAnswer" class="ansBlock" style="display: none;">');
        form::tag("", "hr");
        form::html('<div class="form-group">
                                <label class="control-label col-sm-3 col-md-2">Answer:</label>
                                <div class="col-sm-8 col-md-7 col-lg-5">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" id="ansName" value="">
                                        </div>
                                        <div class="col-sm-3 no-padding">
                                            <button style="width: 100%;" class="btn btn-default btn-danger" onClick="removeAnswer($(this));">Delete answer</button>
                                        </div>
                                    </div>                                                                       
                                </div>
                   </div>');
        form::inpCheckList(
            "Reject for:",
            "ansPositions",
            $qPositions,
            [],
            array_merge($args, ["check-lbl-class" => "small", "id" => "ansPositions"])
        );

        form::html('</div>');
        form::html('<hr/>');
        form::html('<div class="form-group">
                                <label class="control-label col-sm-3 col-md-2"></label>
                                <div class="col-sm-8 col-md-7 col-lg-5">
                                    <button class="btn btn-default btn-success" id="addNewAnswer">Add new answer</button>
                                </div>
                   </div>');


        form::html("<hr/>");


        form::inpSaveCancel(ADMIN . $pg["list"]["url"], self::$saveCancelArgsBase);

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));


    }

    public static function AnswerDetails($data, $pg, $lbls, $lists)
    {
        $args = self::$fldArgsBase;
        $allPositions = [];
        foreach ($data["allpositions"] as $item) {
            $allPositions[$item["pID"]] = htmlspecialchars($item["pName"]);
        }
        $aPositions = [];

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
        $text = htmlspecialchars($data[QuestionModel::$dt["pfx"] . "Name"]);

        form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        form::openDiv(["class" => "col-sm-12 col-md-12 col-lg-12"]);
        form::tag($text, "h4");
        form::closeDiv(["class" => "col-sm-12 col-md-12 col-lg-12"]);
        form::closeDiv(array("class" => "fld form-group"));
        form::closeDiv();

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );

        form::inpCheckList(
            "Positions",
            "positions",
            $allPositions,
            $aPositions,
            $args
        );

        form::inpSaveCancel(ADMIN . $pg["list"]["url"], self::$saveCancelArgsBase);
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));


    }
}
