function validateCompleteForm(objForm, strErrorClass) {return _validateInternal(objForm, strErrorClass, 0);};
/*function validateStandard(objForm, strErrorClass) {return _validateInternal(objForm, strErrorClass, 1);};*/

function setValidationAndVisability(formIdOrObj, fldOrList, valSetStatus, visSetStatus){
    var allowedStatuses = ["enable","disable"];
    /* Defaults: */
    valSetStatus = allowedStatuses.indexOf(valSetStatus) > 0 ? valSetStatus : "disable";
    visSetStatus = allowedStatuses.indexOf(visSetStatus) > 0 ? visSetStatus : "disable";
    formObj = (typeof formIdOrObj === "string") ? document.getElementById(formIdOrObj) : formIdOrObj;
    fldsList = (typeof fldOrList === "string") ? [fldOrList] : fldOrList;
    for(i = 0; i < fldsList.length; i++){
        fld = formObj.elements.namedItem(fldsList[i]);
        if(valSetStatus === "disable"){
            fld.setAttribute("data-name",fld.getAttribute("name"));
            fld.removeAttribute("name");
        } else {
            fld.setAttribute("name",fld.getAttribute("data-name"));
            fld.removeAttribute("data-name");            
        }
        /*if(visSetStatus === "disable"){
            fld
        } else {
            
        }*/
    }
}

function setFormFieldError(form, fieldName, error){
    var field = form.elements[fieldName];
    field.className = (field.className.length>0 ? " " :"") + "error"; 
    field.nextSibling.innerHTML = error;
    addTempClass(field.nextSibling,"blink");
    if (!field.getAttribute("onchange")) {
        field.setAttribute("onchange","validateFld(this)");
    }
}

var bError = false;
function _validateInternal(form, strErrorClass, nErrorThrowType) {
    var strErrorMessage = "";
    var objFirstError = null;
    /*if (nErrorThrowType == 0) {
        strErrorMessage = (form.err) ? form.err : _getLanguageText("err_form");
    };*/
    var fields = _GenerateFormFields(form);
    for (var i = 0; i < fields.length; ++i) {
        var field = fields[i];
        if (!field.IsValid(fields)) { 
            field.SetClass(strErrorClass);
            if (nErrorThrowType === 1) {
                _throwError(field);
                return false;
            } else {
                if (objFirstError === null) {
                    objFirstError = field;
                }
                strErrorMessage = _handleError(field, strErrorMessage);
                bError = true;
                field.element.nextSibling.innerHTML = strErrorMessage;
                addTempClass(field.element.nextSibling,"blink");
                if (!field.element.getAttribute("onchange")) {
                    field.element.setAttribute("onchange","validateFld(this)");
                }
            }
        } else {
            field.ResetClass(strErrorClass);
            if(field.element.nextSibling && field.element.nextSibling.className==="err"){
                addTempClass(field.element.nextSibling,"fadeout");
            }
        }
    };
    if (objFirstError !== null) {
        alert(strErrorMessage);
        objFirstError.element.focus();
        return false;
    };
    return true;
};

function addTempClass(el,cName){
    //console.log(el, cName);
    if(el.getAttribute("class").indexOf(cName)>=0){
        el.setAttribute("class",el.getAttribute("class").replace(cName,"").trim());
    }
    el.setAttribute("class",el.getAttribute("class") + " "+cName);
    setTimeout(function(thisObj){thisObj.setAttribute("class",thisObj.getAttribute("class").replace(cName,"").trim());},800,el);
    if(cName === "fadeout"){
        setTimeout(function(thisObj){thisObj.innerHTML="";},700,el);
    }
};

function validateFld(field){
    var strErrorMessage = "";
    var form = field.form;
    var fields = _GenerateFormFields(form);
    for (var i=0; i<form.elements.length; i++) {
        if (field === form.elements[i]){
            field = fields[i];
        }
    }
    //console.log(field, field.IsValid);
    if (!field.IsValid(fields)) {
        field.SetClass('error');
        strErrorMessage = _handleError(field, strErrorMessage);
        field.element.nextSibling.innerHTML = strErrorMessage;
        addTempClass(field.element.nextSibling,"blink");
    } else {
        field.ResetClass('error');
        //console.log(field.element, field.element.nextSibling, field); 
        addTempClass(field.element.nextSibling,"fadeout");
        
    }
    //console.log(field, field.nextSibling);
};

function _getLanguageText(id) {
    objTextsInternal = new _jsVal_Language();
    objTexts = null;
    try {
        objTexts = new jsVal_Language();
    } catch (ignored) {
    }
    ;
    switch (id) {
        case "err_form":
            strResult = (!objTexts || !objTexts.err_form) ? objTextsInternal.err_form : objTexts.err_form;
            break;
        case "err_enter":
            strResult = (!objTexts || !objTexts.err_enter) ? objTextsInternal.err_enter : objTexts.err_enter;
            break;
        case "err_select":
            strResult = (!objTexts || !objTexts.err_select) ? objTextsInternal.err_select : objTexts.err_select;
            break;
    }
    ;
    return strResult;
};

function _GenerateFormFields(form) {
    var arr = new Array();
    for (var i = 0; i < form.length; ++i) {
        var element = form.elements[i];
        var index = _getElementIndex(arr, element);
        if (index === -1) {
         //   arr[arr.length] = new Field(element, form);
        } else {
            arr[index].Merge(element);
        };
    };
    return arr;
};

function _getElementIndex(arr, element) {
    if (element.name) {
        var elementName = element.name.toLowerCase();
        for (var i = 0; i < arr.length; ++i) {
            if (arr[i].element.name) {
                if (arr[i].element.name.toLowerCase() === elementName) {
                    return i;
                }
            };
        };
    }
    return -1;
};

function _jsVal_Language() {
    this.err_form = "Please enter/select values for the following fields:\n\n";
    this.err_select = "Please select a valid \"%FIELDNAME%\"";
    this.err_enter = "Please enter a valid \"%FIELDNAME%\"";
};

function Field(element, form) {
    this.type = element.type;
    this.element = element;
    this.exclude = element.exclude || element.getAttribute('data-exclude');
    this.err = element.err || element.getAttribute('err');
    this.required = _parseBoolean(element.required || element.getAttribute('required'));
    this.realname = element.realname || element.getAttribute('realname');
    this.elements = new Array();
    switch (this.type) {
        case "textarea":
        case "password":
        case "text":
        case "email":
        case "file":
            this.value = element.value;
            this.minLength = element.minlength || element.getAttribute('data-minlength');
            this.maxLength = element.maxlength || element.getAttribute('maxlength');
            this.regexp = this._getRegEx(element);
            this.minValue = element.minvalue || element.getAttribute('data-minvalue');
            this.maxValue = element.maxvalue || element.getAttribute('data-maxvalue');
            this.equals = element.equals || element.getAttribute('data-equals');
            this.callback = element.callback || element.getAttribute('data-callback');
            break;
        case "select-one":
        case "select-multiple":
            this.values = new Array();
            for (var i = 0; i < element.options.length; ++i) {
                if (element.options[i].selected && (!this.exclude || element.options[i].value !== this.exclude)) {
                    this.values[this.values.length] = element.options[i].value;
                }
            }
            this.min = element.min || element.getAttribute('data-min');
            this.max = element.max || element.getAttribute('data-max');
            this.equals = element.equals || element.getAttribute('data-equals');
            break;
        case "checkbox":
            this.min = element.min || element.getAttribute('data-min');
            this.max = element.max || element.getAttribute('data-max');
        case "radio":
            this.required = _parseBoolean(this.required || element.getAttribute('required'));
            this.values = new Array();
            if (element.checked) {
                this.values[0] = element.value;
            }
            this.elements[0] = element;
            break;
    };
};

Field.prototype.Merge = function(element) {
    var required = _parseBoolean(element.getAttribute('required'));
    if (required) {
        this.required = true;
    };
    if (!this.err) {
        this.err = element.getAttribute('err');
    };
    if (!this.equals) {
        this.equals = element.getAttribute('data-equals');
    };
    if (!this.callback) {
        this.callback = element.getAttribute('data-callback');
    };
    if (!this.realname) {
        this.realname = element.getAttribute('realname');
    };
    if (!this.max) {
        this.max = element.getAttribute('data-max');
    };
    if (!this.min) {
        this.min = element.getAttribute('data-min');
    };
    if (!this.regexp) {
        this.regexp = this._getRegEx(element);
    };
    if (element.checked) {
        this.values[this.values.length] = element.value;
    };
    this.elements[this.elements.length] = element;
};

Field.prototype.IsValid = function(arrFields) {
    switch (this.type) {
        case "textarea":
        case "password":
        case "email":
        case "text":
        case "file":
            return this._ValidateText(arrFields);
        case "select-one":
        case "select-multiple":
        case "radio":
        case "checkbox":
            return this._ValidateGroup(arrFields);
        default:
            return true;
    };
};
Field.prototype.SetClass = function(newClassName) {
    if ((newClassName) && (newClassName !== "")) {
        if ((this.elements) && (this.elements.length > 0)) {
            for (var i = 0; i < this.elements.length; ++i) {
                if (this.elements[i].className.indexOf(newClassName) < 0) {
                    //this.elements[i].oldClassName = this.elements[i].className;
                    this.elements[i].className += ((this.elements[i].className.length > 0 ? " " : "") + newClassName);
                }
            }
        } else {
            if (this.element.className.indexOf(newClassName) < 0) {
                //this.element.oldClassName = this.element.className;
                this.element.className += ((this.element.className.length > 0 ? " " : "") + newClassName);
            }
        }
        ;
    }
};
Field.prototype.ResetClass = function(errClassName) {
    if ((this.type !== "button") && (this.type !== "submit") && (this.type !== "reset")) {
        if ((this.elements) && (this.elements.length > 0)) {
            for (var i = 0; i < this.elements.length; ++i) {
                if (this.elements[i].className.indexOf(errClassName) >= 0) {
                    this.elements[i].className = this.elements[i].className.replace(errClassName,"").trim();
                    if(this.elements[i].className.length===0){
                        this.elements[i].removeAttribute("class");
                    }
                }
            }
        } else {
            if ((this.element)) {
                if (this.element.className.indexOf(errClassName) >= 0) {
                    this.element.className = this.element.className.replace(errClassName,"").trim();
                    if(this.element.className.length===0){
                        this.element.removeAttribute("class");
                    }
                }
                
            }
        };
    };
};
Field.prototype._getRegEx = function(element) {
    regex = element.regexp || element.getAttribute('data-re');
    if (regex === null)
        return null;
    retype = typeof (regex);
    if (retype.toUpperCase() === "FUNCTION")
        return regex;
    else if ((retype.toUpperCase() === "STRING") && !(regex === "JSVAL_RX_EMAIL") && !(regex === "JSVAL_RX_TEL")
            && !(regex === "JSVAL_RX_PC") && !(regex === "JSVAL_RX_ZIP") && !(regex === "JSVAL_RX_MONEY") && !(regex === "JSVAL_RX_MONEY_COMMAS")
            && !(regex === "JSVAL_RX_CREDITCARD") && !(regex === "JSVAL_RX_POSTALZIP") && !(regex === "JSVAL_NAME"))
    {
        nBegin = 0;
        nEnd = regex.length - 1;
        if (regex.charAt(0) === "/")
            nBegin = 1;
        if (regex.charAt(regex.length - 1) === "/")
            nEnd = regex.length - 1;
        return new RegExp(regex.slice(nBegin, nEnd));
    } else {
        return regex;
    }
    ;
};
Field.prototype._ValidateText = function(arrFields) {
    if ((this.required) && (this.callback)) {
        nCurId = this.element.id ? this.element.id : "";
        nCurName = this.element.name ? this.element.name : "";
        nCurValue = this.value ? this.value : "";
        eval("bResult = " + this.callback + "('" + nCurId + "', '" + nCurName + "', '" + this.value + "');");
        if (bResult === false) {
            return false;
        };
    } else if(this.callback) {
        nCurId = this.element.id ? this.element.id : "";
        nCurName = this.element.name ? this.element.name : "";
        nCurEl = this.element;
        eval("bResult = " + this.callback + "(this.element,'" + nCurName + "','" + nCurId + "');");
        if (bResult === false) {
            this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-callback"];
            return false;
        };
    } else {
        if (this.required && !this.value) {
            this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["required"];
            return false;
        };
        if (this.value && (this.minLength && this.value.length < this.minLength)) {
            this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-minlength"];
            return false;
        };
        if (this.value && (this.maxLength && this.value.length > this.maxLength)) {
            this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["maxlength"];
            return false;
        };
        if (this.regexp) {
            if (!_checkRegExp(this.regexp, this.value))
            {
                this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-re"];
                if (!this.required && this.value) {
                    return false;
                }
                if (this.required) {
                    return false;
                }
            }
            else
            {
                return true;
            };
        };
        if (this.equals) {
            for (var i = 0; i < arrFields.length; ++i) {
                var field = arrFields[i];
                if ((field.element.name === this.equals) || (field.element.id === this.equals)) {
                    this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-equals"];
                    if (field.element.value !== this.value) {
                        return false;
                    };
                    break;
                };
            };
        };
        if (this.required) {
            var fValue = parseFloat(this.value);
            if ((this.minValue || this.maxValue) && isNaN(fValue)) {
                return false;
            };
            if ((this.minValue) && (fValue < this.minValue)) {
                return false;
            };
            if ((this.maxValue) && (fValue > this.maxValue)) {
                return false;
            };
        };
    }
    return true;
};

Field.prototype._ValidateGroup = function(arrFields) {
    if (this.required && this.values.length === 0) {
        this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["required"];
        console.log(this);
        console.log("1");
        return false;
    };
    if (this.required && this.min && this.min > this.values.length) {
        this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-min"];
        console.log("2");
        return false;
    };
    if (this.required && this.max && this.max < this.values.length) {
        this.errormsg = window["msgs"+this.element.form.getAttribute("data-no")][this.element.getAttribute("name")]["data-max"];
        console.log("3");
        return false;
    };
    return true;
};

function _handleError(field, strErrorMessage) {
    strNewMessage = field.errormsg;
    //strNewMessage = strErrorMessage + ((field.realname) ? field.realname : ((obj.id) ? obj.id : obj.name)) + "\n";
    return strNewMessage;
};

function _throwError(field) {
    var obj = field.element;
    switch (field.type) {
        case "text":
        case "email":
        case "password":
        case "textarea":
        case "file":
            alert(_getError(field, "err_enter"));
            try {
                obj.focus();
            }
            catch (ignore) {
            }
            break;
        case "select-one":
        case "select-multiple":
        case "radio":
        case "checkbox":
            alert(_getError(field, "err_select"));
            break;
    };
};

function _getError(field, str) {
    var obj = field.element;
    strErrorTemp = (field.err) ? field.err : _getLanguageText(str);
    idx = strErrorTemp.indexOf("\\n");
    while (idx > -1) {
        strErrorTemp = strErrorTemp.replace("\\n", "\n");
        idx = strErrorTemp.indexOf("\\n");
    };
    return strErrorTemp.replace("%FIELDNAME%", (field.realname) ? field.realname : ((obj.id) ? obj.id : obj.name));
};

function _parseBoolean(value) {
    value = value==="" ? true : value; /* Fix for old IE */
    return !(!value  || value === 0 || value === "0" || value === "false");
};

function _checkRegExp(regx, value) {
    console.log(regx, value);
    switch (regx) {
        case "JSVAL_RX_EMAIL":
            return((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/).test(value));
        case "JSVAL_RX_TEL":
            return((/^1?[\-]?\(?\d{3}\)?[\-]?\d{3}[\-]?\d{4}$/).test(value));
        case "JSVAL_RX_PC":
            return((/^[a-z]\d[a-z]?\d[a-z]\d$/i).test(value));
        case "JSVAL_RX_ZIP":
            return((/^\d{5}$/).test(value));
        case "JSVAL_RX_MONEY":
            return((/^\d+([\.]\d\d)?$/).test(value));
        case "/JSVAL_RX_MONEY_COMMA/":
            return((/^\s*[$]?\s*((\d+)|(\d{1,3}(\,\d{3})+))(\.\d{1})?(\d{1})?\s*$/).test(value))
        case "JSVAL_NAME_LATIN":
            return((/^([a-zA-Z]+)([a-zA-Z\'\-\ &]+)$/).test(value));
        case "JSVAL_NAME":
            return((/^([a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð]+)([a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð\'\-\ &]+)$/).test(value));
        case "JSVAL_RX_CREDITCARD":
            return(!isNaN(value));
        case "JSVAL_RX_POSTALZIP":
            if (value.length === 6 || value.length === 7)
                return((/^[a-zA-Z]\d[a-zA-Z] ?\d[a-zA-Z]\d$/).test(value));
            if (value.length === 5 || value.length === 10)
                return((/^\d{5}(\-\d{4})?$/).test(value));
            break;
        default:
            //console.log(regx);
            return(regx.test(value));
    };
};