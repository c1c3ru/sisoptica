var telefones_count = 1;
function clearTelefones(){
    $("#telefones-space").html("");
    telefones_count = 0;
    addTelefone();
}
function addTelefone(){
    var row = "<div class='telefone-row'>";
    row += "<input type='text' class='input text-input' name='telefone-"+(++telefones_count)+"' maxlength='13' id='telefone-"+telefones_count+"' style='width:70%' onkeypress='MascaraTelefone(this)'/>";
    row += "<span class='h-separator'> &nbsp; </span>";
    row += "<span id='add-telefone' onclick='addTelefone()'>+</span>";
    row += "</div>";
    $("#add-telefone").remove();
    $("#telefones-space").append(row);
}