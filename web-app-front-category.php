<?php
/** front category page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The front page class
 * @example http://www.example.com/... */
class CategoryPage extends PageFront {
    
    const CONTROLLER = "CategoryFrontController";
    
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();
        
        $M = App_AdminModel::GetModel($C);
        
        $C::$data = $M::DataByUrl(filter_input(INPUT_GET, "rq"));
        $C::$id =   isset($C::$data[$M::$dt["key"]]) ? (int)$C::$data[$M::$dt["key"]] : $this->web404();
        if(isset($C::$data[$M::$dt["pfx"]."CountProducts"])){
            ProductModel::$dataAddons[] = ProductModel::$dt["pfx"]."Manufacturer";
            ProductModel::$dataAddons[] = ProductModel::$dt["pfx"]."Vendor";
            ProductModel::$dataAddons[] = ProductModel::$dt["pfx"]."Price";
            $C::$data["products"] = ProductModel::data("`".ProductModel::$dt["pfx"]."CID`=".$C::$id." AND  `pActive`=\"Yes\"");
        } else {
            $C::$data["subs"] = $M::SubCatsData();
        }
    }
    
    protected function inHead($C = self::CONTROLLER) {
        if(in_array($C::$rq, array("login", "recover"))){
            msg::prepare();
        }
        cf::appendToHeadTag(ADD."category.js", "script", array("defer"=>true));
        cf::appendToHeadTag(ADD."jquery.magnific-popup.js", "script", array("defer"=>true));
        cf::appendToHeadTag(ADD."magnific-popup.css", "css");
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        if(filter_has_var(INPUT_GET, "addtocart")){
            cf::$pg["ext"] = "json"; //Prevent headers from loading
        }
        
        self::$pageToControllerMethods = array("Category", "ProductAdd2Cart");
        $this->setControlerProperties(self::$controller = $C, array(/*"rq", */"pg"));
    }

}

$page = new CategoryPage();

$page->HTMLHeader();

if(filter_has_var(INPUT_GET, "addtocart")){
    $page->ProductAdd2Cart();
} else {
    $_SESSION['last_added_item_url'] = cf::$rq["url-full"];
    $page->Category();
}

$page->HTMLFooter();
