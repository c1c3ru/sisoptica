<h4> Busque por clientes: </h4>
<div id="operations-search">
    <label> Nome: <input type="text" placeholder="Digite o Nome do cliente" 
                    onkeypress="if(event.keyCode == 13){buscarClientes();}" 
                    class="input text-input medium-input" id="nome-busca"/> </label>
    <button onclick="buscarClientes()" style="padding: 5px;" id='btn-busca-on'> 
        <img src="<?php echo GRID_ICONS?>visualizar.png"/>
    </button>
    <a href="javascript:;" id="add-cliente-link" style="text-align: center"
       title="Adicionar Cliente" onclick="openCadCliente()"> 
        <img src="images/main-toolbar/add-cliente.png" width="40px">
        <p style="font-size: 9pt; margin-top: -10px;"> <span>Add Cliente</span> </p>
    </a>
</div>
<div id="results-busca-por-cliente"> </div>    
<script>
function buscarClientes(){
    var url = "ajax.php?code=9987&nome="+$("#nome-busca").val();
    openLoadingState();
    get(url, function(data){
       if(data.code == "0"){
           var clientes = data.data;
           if(!clientes.length){
                var html = "<p style='text-align:center;color:gray;'>Sem Resultados (";
                html += "<a href='javascript:;' onclick='openCadCliente()'>Cadastrar?</a>"
                html += ")</p>";
                $("#results-busca-por-cliente").html(html);
           } else {
                $("#results-busca-por-cliente").html("");
                for(i = 0, l = clientes.length; i < l; i++){
                    var row = document.createElement("div");
                    var strrow =  "";
                    if(clientes[i].bloqueado != "0"){
                        row.setAttribute("class", "row-result-block");
                        strrow =  "<label><span>"+clientes[i].nome+" (BLOQUEADO) </span>";
                        strrow += "<span style='font-size:9pt;;float:right;'>CPF ("+clientes[i].cpf+")</span></label>";
                    } else {
                        row.setAttribute("class", "row-result");
                        var nome = ''; var endereco = ''; var localidade = '';
                        if(clientes[i].nome){
                            nome = clientes[i].nome.replace("'", "\\'");
                        }
                        if(clientes[i].endereco){
                            endereco = clientes[i].endereco.replace("'", "\\'");
                        }
                        if(clientes[i].localidade){
                            localidade = clientes[i].localidade.replace("'", "\\'");
                        }
                        var param = "'"+nome+"','"+localidade+"','"+endereco+"', "+clientes[i].id;
                        strrow =  "<label title='Selecionar' onclick=\"selectCliente("+param+")\"><span>"+clientes[i].nome+"</span>";
                        strrow += "<span style='font-size:9pt;;float:right;'>CPF ("+clientes[i].cpf+")</span></label>";
                    }
                    row.innerHTML = strrow;
                    $("#results-busca-por-cliente").append(row);
                }
          }    
       }
       else badAlert(data.message);
       endLoadingState();
    });
}
function openCadCliente(){
    var url = "ajax.php?code=4499"
    openLoadingState();
    getHTML(url, function(data){
        expandViewDataMode();
        $("#results-busca-por-cliente").html(data);
        endLoadingState();
    });
}
function openLoadingState(){
    openLoadingInElement("#results-busca-por-cliente");
    $("#nome-busca").attr("disabled", true);
    $("#btn-busca-on").attr("disabled", true);
}
function endLoadingState(){
    $("#nome-busca").attr("disabled", false);
    $("#btn-busca-on").attr("disabled", false);
}
$("#nome-busca").focus();
</script>
<style> 
#operations-search {padding: 10px;}
#operations-search button{vertical-align: middle}
#results-busca-por-cliente{margin-top: 25px;}    
.row-result{ padding: 10px; border-top: lightgrey solid 1px;}
.row-result:hover{background: #eee; box-shadow: inset 0 1px 3px white;}
.row-result label:hover span {text-decoration: underline; cursor: pointer;}
.row-result-block{
    padding: 10px; 
    border-top: rgb(68, 4, 4) solid 1px; 
    background: brown;
}
.row-result-block span {
    color: white !important;
    text-shadow: 1px -1px 1px rgb(68, 4, 4);
}
#add-cliente-link{float: right;}
</style>