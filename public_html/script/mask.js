function MascaraCPF(cpf){
    if(mascaraInteiro(cpf)==false){
        event.returnValue = false;
    }       
    return formataCampo(cpf, '000.000.000-00', event);
}
function MascaraTelefone(tel){
    if(mascaraInteiro(tel)==false){
        event.returnValue = false;
    }       
    return formataCampo(tel, '(00) 00000-0000', event);
}
function MascaraCNPJ(cnpj){
    if(mascaraInteiro(cnpj)==false){
        event.returnValue = false;
    }       
    return formataCampo(cnpj, '00.000.000/0000-00', event);
}
function MascaraCEP(cep){
    if(mascaraInteiro(cep)==false){
        event.returnValue = false;
    }       
    return formataCampo(cep, '00.000-000', event);
}
function mascaraFloat(){
    if (event.keyCode < 48 || event.keyCode > 57){
        if(event.keyCode >= 44 && event.keyCode <= 46) { return true; }
        event.returnValue = false;
        return false;
    }
    return true;
}
function mascaraInteiro(){
    if (event.keyCode < 48 || event.keyCode > 57){
        if(event.keyCode == 45) { return true; }
        event.returnValue = false;
        return false;
    }
    return true;
}
function formataCampo(campo, Mascara, evento) { 
    var boleanoMascara; 

    var Digitato = evento.keyCode;
    exp = /\-|\.|\/|\(|\)| /g
    campoSoNumeros = campo.value.toString().replace( exp, "" ); 

    var posicaoCampo = 0;    
    var NovoValorCampo="";
    var TamanhoMascara = campoSoNumeros.length;; 

    if (Digitato != 8) { // backspace 
            for(i=0; i<= TamanhoMascara; i++) { 
            boleanoMascara  = ((Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".")
                                || (Mascara.charAt(i) == "/")) 
            boleanoMascara  = boleanoMascara || ((Mascara.charAt(i) == "(") 
                            || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " ")) 
            if (boleanoMascara) { 
                NovoValorCampo += Mascara.charAt(i); 
                TamanhoMascara++;
            }else { 
                NovoValorCampo += campoSoNumeros.charAt(posicaoCampo); 
                posicaoCampo++; 
            }              
        }      
        campo.value = NovoValorCampo;
        return true; 
    }else { 
        return true; 
    }
}
function adjustPrefix(target){
    var prefixs = ["RUA ", "AV. ", "ROD. "];
    var input = document.getElementById('prefix-end');
    var prefix = prefixs[input.selectedIndex];
    target = document.getElementById(target);
    var actualText = target.value.toUpperCase();
    for(i = 0; i < prefixs.length; i++){
        var idx = actualText.indexOf(prefixs[i]);
        if(idx != -1){
            actualText = actualText.substring(idx+prefixs[i].length);
            break;
        }
    }
    target.value = prefix+actualText;
}
function toMoney(fvalue){
    var str = (new String(fvalue)).replace(".", ",");
    var p = str.split(",");
    if(p.length == 1) p[1] = "00";
    else if(p[1].length == 1) p[1] += "0";
    else p[1] = p[1].substr(0, 2);
    var res = "R$ "+p.join(",");
    return res;
}