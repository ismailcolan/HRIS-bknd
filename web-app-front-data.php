<?php 
/** The front Directory
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-topic"); */


set_time_limit(0);
ini_set('memory_limit', '-1');
/** The Front Directory class
 * @example http://www.example.com/... */
class FrontData extends PageFront {
    
    //protected $req; -> must be changed to slef::$rq
    const USE_GZIP = true;
    const CONTROLLER = "ProductController";
    
    public function __construct() {
        parent::__construct();
    
        $this->time_start = null;
    }
    
    protected function inHead() {
        //cf::appendToHeadTag("//npmcdn.com/imagesloaded@4.1/imagesloaded.pkgd.min.js", "script", array("defer"=>true));
        //cf::appendToHeadTag("//npmcdn.com/masonry-layout@4.0/dist/masonry.pkgd.min.js", "script", array("defer"=>true));
    }

    protected function setPgRq($C = self::CONTROLLER){
        if(!in_array(cf::$rq["url"], array("product-search"))){
            $this->web404();
        } 
        self::$pageToControllerMethods = array("DataJson");
        $this->setControlerProperties($C, array("pg", "id", "data", "rq"));
    }
}

$frontSection = new FrontData();

$frontSection->gzipStartStop("start");

$frontSection->DataJson();

$frontSection->gzipStartStop("stop");