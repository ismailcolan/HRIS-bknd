<?php
/** The paging helper class */
class paging{
    public $total, $current; private $perPage, $range, $output = array();

    public function __construct($rows, $current, $perPage, $range = 10){
        $this->total = $rows;
        $this->perPage = $perPage;
        $this->range = $range;
        if($current <= $this->calculateTotal()){
            $this->current = $current;
        }else{
            $this->current = $this->calculateTotal();
        }
        if($this->current < 1){
            $this->current = 1;
        }
    }

    private function calculateTotal(){
         return ceil((int) $this->total / $this->perPage);
    }

    private function parse($base, $query_str, $hash){
        $url = $base . $query_str;
        $pages = $this->calculateTotal();
        if($pages > 1){
            if ($this->current < 1) {
                return;
            } elseif ($this->current > $pages) {
                return;
            }
            $this->output[] = '<div style="clear:both" ></div><ul class="pagination" id="paging">';
            $this->output[] = '<li class="disabled"><a href="#paging">PAGE</a></li>';
            // $this->output[] = '<li '.($this->current == 1 ? 'class="active"' : '').'><a href="'.$url.'">1</a></li>';
            $end = $this->current + $this->range - 1;
            if($end < 1){
                $end =  1;
            }
            if($end > $pages){
                $end = $pages;
            }
            $ranges = array(
                array(1,10),
                array(11,20),
                array(21,30),
                array(31,40),
                array(41,50),
                array(51,60),
                array(61,70),
                array(81,90),
                array(91,100)
            );
            if($this->current >= $pages){
                $start = $pages - $this->range;
            }else{
                $start = $this->current;
                foreach($ranges as $range){
                    if($this->current >= $range[0] && $this->current <= $range[1]){
                        $start = $range[0];
                        $end = $range[1];
                        break;
                    }
                }
            }
            if($start < 1){
                $start = 1;
            }
            for($i=$start;$i <= $end && $i <= $pages;$i++){
                $href = ($url . ( $i>1 ?((!empty($query_str) ? '&amp;':'?').'page=' . $i . $hash) : ''));
                if($i <= $pages){
                        $this->output[] = '<li '.($this->current == $i ? 'class="active"' : '').'><a href="'.$href.'">'.$i.'</a></li>';
                }
            }
            if(($end+1) <= $pages){
                $this->output[] = '<li><a href="'.(!empty($query_str) ? $url . '&amp;page=' . ($end+1) : $url . '?page=' . ($end+1)) . $hash .'">NEXT</a></li>';
            }
            $this->output[] = '</ul>';
        }
    }

    public function getLimit(){
        $limit = " LIMIT " . (($this->current - 1) * $this->perPage) .', '.$this->perPage;
        return $limit;
    }

    public function render($base, $query_str = '', $hash=''){
        $this->parse($base, $query_str, $hash);
        return implode("\r\n", $this->output);
    }

}
?>