<?php
class HTMLUtil {
    
    public static function grid_icon($img_name, $title = ""){
        return img(GRID_ICONS.$img_name, $title);
    }
    
    public static function img($src, $title = "", $alt = ""){
        return "<img src=\"$src\" title=\"$title\" alt=\"$alt\">";
    }
    
    public static function hsep(){
        return "<span class='h-separator'>&nbsp;</span>";
    }
    
    public static function vsep(){
        return "<p class='v-separator'>&nbsp;</p>";
    }
    
    public static function title_form($title, $tag = "p"){
        return "<$tag  class='title-form'>$title</$tag>";
    }
    
    public static function select($values, $properties){
        $select = "<select ";
        foreach($properties as $p => $v){
            $select .= "$p=\"$v\" ";
        }
        $select .= ">";
        foreach($values as $v => $content){
            $option = "<option ";
            if(is_array($content)){ $option .= " selected>".$content[0]; }
            else { $option .= " value='$v'> $content "; }
            $option .= "</option>";
            $select .= $option;
        }
        $select .= "</select>";
        return $select;
    }
    
    public static function link($href, $content, $new_window = false){
        return "<a href=\"$href\" ".($new_window?"target='_blank'":"").">$content</a>";
    }
    
    public static function js_link($onclick, $content){
        return "<a href=\"javascript:;\" onclick=\"$onclick\">$content</a>";
    }
    
}

function grid_icon($img_name, $title = ""){ return HTMLUtil::grid_icon($img_name, $title); }
function img($img_name, $title = "", $alt = ""){ return HTMLUtil::img($img_name, $title, $alt); }
function hsep(){ return HTMLUtil::hsep(); }
function vsep(){ return HTMLUtil::vsep(); }
function select($values, $properties){ return HTMLUtil::select($values, $properties); }
function title_form($title, $tag="p"){ return HTMLUtil::title_form($title, $tag); }
function p_title_form($title){ return HTMLUtil::title_form($title); }
function a_link($href, $content, $new_window = false){ return HTMLUtil::link($href, $content, $new_window); }
function js_link($onclick, $content){ return HTMLUtil::js_link($onclick, $content); }

?>
