function openAddSpaceForm(btn, update_mode){
    var frameid = btn.parentNode.getAttribute("form");
    if($(btn.parentNode).children(".cancel-btn-tool-box").css("display") == "none")
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'inline-block'});
    else
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'none'});
    var addSpace = $("#"+frameid).children(".add-space-this-form");
    var submit = $("#"+frameid+" .submit");
    if(update_mode){
        submit.val("Atualizar");
    }else{
        clearForm(frameid);
        if(typeof clearTelefones == 'function')
            clearTelefones();
        submit.val("Cadastrar");
    }
    $("#add-btn-tool").css("display","none");
    if(update_mode && addSpace.is(":visible")){ 
        $(btn.parentNode).children(".cancel-btn-tool-box").css({display:'inline-block'});
        return;
    }
    addSpace.slideToggle("fast");
}
function closeAddSpaceForm(btn){
    var frameid = btn.parentNode.getAttribute("form");
    if($(btn).css("display") == "none")
        $(btn).css({display:'inline-block'});
    else{
        $("#add-btn-tool").css("display","inline-block");
        $(btn).css({display:'none'}); 
    }
    clearForm(frameid);
    var submit = $("#"+frameid+" .submit");
    submit.val("Cadastrar");
    var addSpace = $("#"+frameid).children(".add-space-this-form");
    addSpace.slideToggle("fast");
}
