<?php
class GenericTable {
    
    private $headers; //an dictionary of <k => property_data, v => header>
    private $data; // an array of arrays
    public $id;
    
    private $onclick = null;
    private $id_property = null; 
    
    public function __construct(array $headers = array(), array $data = array()) {
        $this->headers = $headers;
        if(count($data)) {
            foreach ($data as $entrie) {
                $this->addRow($entrie);
            }
        }
    }
    
    public function addHeader($header, $property){
        if(array_key_exists($property, $this->headers)) return false;
        $this->headers[$property] = $header;
    }
    
    public function addRow($dataInstance, $idx = -1){
        if(is_object($dataInstance)) $properties = get_object_vars($dataInstance) ;
        else if(is_array($dataInstance)) $properties = $dataInstance;
        
        if( empty($properties) || count(array_diff_key($this->headers, $properties)) )
            return false;
        if($idx == -1) $this->data[] = $dataInstance;
        else if($idx >= 0) $this->data[$idx] = $$dataInstance;
        else return false;
        return true;
    }
    
    public function removeRow($idx = -1){
        if($idx == -1) array_pop($this->data);
        else if($idx >= 0) unset($this->data[$idx]);
        else return false;
        return true;
    }
    
    public function setActionRow($functionName, $identifyProperty){
        $this->onclick = $functionName;
        $this->id_property = $identifyProperty;
    }
    
    public function redefineStyleProperty($property, $value){
        if(array_key_exists($property, $this->style))
            $this->style[$property] = $value;
    }
    
    public function draw(){
        echo "<table class='generic-table' id='{$this->id}' cellspacing='0' cellpadding='0'>";
        echo "<thead>";
        echo "<tr class='title-section-generic-table'>";
        foreach ($this->headers as $value) {
            echo "<th> $value </th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $properties = array_keys($this->headers);
        if(!is_null($this->id_property)) {
            for($i = 0, $l = count($this->data); $i < $l; $i++){
                $entrie = (object) $this->data[$i];
                echo "<tr class='data-row-generic-table' ";
                echo "onclick=\"{$this->onclick}('{$entrie->{ $this->id_property} }', this)\"";
                echo " selected = \"0\" >";
                foreach ($properties as $property) {
                    if(isset($entrie->$property)) echo "<td>{$entrie->$property}</td>";
                    else echo "<td> </td>";
                }
                echo "</tr>";
            }  
        } else {
            for($i = 0, $l = count($this->data); $i < $l; $i++){
                $entrie = (object) $this->data[$i];
                echo "<tr class='data-row-generic-table'>";
                foreach ($properties as $property) {
                    if(isset($entrie->$property)) echo "<td>{$entrie->$property}</td>";
                    else echo "<td> </td>";
                }
                echo "</tr>";
            }
        }
        echo "</tbody>";
        echo "</table>";
    }
    
}
?>