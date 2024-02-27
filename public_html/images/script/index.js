var out = document.getElementById("saida");
$(".parent-op-top-menu").hover(function(){	
	$(this).addClass("parent-op-top-menu-hover fnt-white");
	var lis = $(this).children(".sub-menu");
	$(lis).stop(true, true).slideToggle(200);	
}, function() {
	var lis = $(this).children(".sub-menu");
	$(lis).stop(true, true).slideToggle("fast");	
	$(this).removeClass("parent-op-top-menu-hover fnt-white");
});

$(".sub-menu-item").hover(function(){
	$(this).addClass("green2-grad-back fnt-white");
}, function() {
	$(this).removeClass("green2-grad-back fnt-white");
});
$(function(){
    checkAsk();
    $("#logout-btn").hover(function(){
        $(this).addClass("brown-grad-back fnt-white");
    }, function(){
        $(this).removeClass("brown-grad-back fnt-white");
    }); 
    
});

function checkAsk() {
    $(".ask").click(function(){
        if(!confirm("Deseja realmente "+$(this).attr("ask")+"?")){
            event.preventDefault();
            event.returnValue = false;
            return false;
        } 
        if($(this).hasClass("pgn")){
            openPasswordGerenteNeed();
        }
    });
}

var ACTION_AFTER = function(){
    alert("Not Setted");
};
var ACTION_AFTER_NOT_CONFIRM = function(){};
var POST_PARAM_AUX;

var els = document.getElementsByClassName("selectable");
for(i = 0, len = els.length; i < len; i++){
	if(els[i].getAttribute("op") == "") continue;
        els[i].onclick = function(){
		var op = this.getAttribute("op");
                window.location = "?op="+op;	
	} 
}

function openPasswordGerenteNeed(){
    openViewDataMode("ajax.php?code=9898");
}

function removeChilds(e){
    while(e.firstChild){
        e.removeChild(e.firstChild);
    }
}

function alert(msg, isBad){
    msg = msg.replace(/\n/g, "<br>");
    document.getElementById("content-alert").innerHTML = msg;
    $("#alert" ).fadeIn('fast');
    window.setTimeout(function(){closeAlert();}, 7500);
}
function badAlert(msg){
    alert("<b class='fnt-brown'>"+msg+"</b>", true);
}
function closeAlert(){
    $("#alert").fadeOut('slow');
} 

function post (url, param, success) {
    $.ajax({
        type: "POST",
        url : url,
        dataType: 'json',
        data: param,    
        success: success,
        error: function (a, b, c) { badAlert(a+"\n"+b+"\n"+c); }
    });
}

function get (url, success) {
	$.ajax({
            type: "GET",
            url : url,
            dataType: 'json',  
            success: success
    });
}
function postHTML (url, param, success) {
    $.ajax({
        type: "POST",
        url : url,
        dataType: 'html',
        data: param,    
        success: success,
        error: function (a, b, c) { badAlert(a+"\n"+b+"\n"+c); }
    });
}

function getHTML (url, success) {
	$.ajax({
            type: "GET",
            url : url,
            dataType: 'html',  
            success: success
    });
}
function clearForm(form_id){
    $("#"+form_id+" input").val("");
    $("#"+form_id+" textarea").val("");
    $("#"+form_id+" select").val("");
    $("#"+form_id+" .checked").attr("checked", true);
    $("#"+form_id+" .notchecked").attr("checked", false);
    $("#"+form_id+" .add-space-this-form .hidden").hide();
    $("#"+form_id+" .lock-edit").hide();
    $("#"+form_id+" .requirable").attr("required",true);
    $("#"+form_id+" .cleanable-space").html("");
    var cleanables = document.getElementById(form_id).getElementsByClassName("cleanable");
    for(var i = 0; i < cleanables.length; i++){
        cleanables[i].innerHTML = cleanables[i].getAttribute("placeholder");
    }
    var defaults = document.getElementById(form_id).getElementsByClassName("with-default");
    for(i = 0; i < defaults.length; i++){
        defaults[i].value = defaults[i].getAttribute("dvalue");
    }
    //Adjusts select field
    var selects = $("#"+form_id+" select");
    for(var j = 0; j < selects.length; j++) {
        var options = $(selects.get(j)).children("option");
        for(var k = 0, l = options.length; k < l; k++){
            if(options[k].getAttribute("selected") != null){
                selects[j].selectedIndex = k;
            }
        }
    }
    $("#"+form_id+" select").change();
}
function openViewDataMode(serv){
    $("#view-data-back").fadeIn("fast");
    openLoadingInElement("#view-data-back .content");
    getHTML(serv, function(data){
        if(data != "") {
            $("#view-data-back .close-data-view").css({left:"72.5%"});
            $("#view-data-back .content").css({width:"48%", left:"25%"}).html(data);
        } else $("#view-data-back").fadeOut("fast");
    });
}

function openLoadingInElement(element_express){
    var loading = "<p style='text-align:center;'><img src='images/loading.gif'/></p>";
    $(element_express).html(loading); 
}

function expandViewDataMode(){
    $("#view-data-back .content").animate({width: "90%", left: "5%"}, 'fast');
    $("#view-data-back .close-data-view").animate({left:"94.5%"}, 'fast');
}

function closeViewDataMode(){
    $("#view-data-back").fadeOut("fast");
    $("#view-data-back .content").html("");
    ACTION_AFTER_NOT_CONFIRM();
    ACTION_AFTER_NOT_CONFIRM = function(){};
}
function floorMoney(floatVlue){
    var str = (new String(floatVlue)).replace(".", ",").replace("R$",'');
    var ps = str.split(",");
    if(ps.length == 1) ps.push("00");
    else if(ps[1].length == 1) ps[1] += "0";
    else ps[1] = ps[1].substr(0, 2);
    return ps.join(".");
}
function floatElement(elementId){
    $("#"+elementId).css({left: event.clientX, top:event.clientY}).fadeIn("fast", function(){
        $('body').bind("click", function(e){
            if(e.target.parentNode.className.indexOf("parent-to-hide") == -1){
                $("#"+elementId).fadeOut("fast");
                $('body').unbind('click');
            }
        });
    });
}
function toUTLParams(obj){
    var strArr = new Array();
    for(var i in obj){
        strArr.push(i+"="+obj[i]);
    }
    return strArr.join("&")
}
function formPostObject(form){
    var postObject  = {};
    $(form).find('input').each(function(){
        postObject[this.name] = this.value;
    });
    $(form).find('select').each(function(){
        postObject[this.name] = this.value;
    });
    $(form).find('textarea').each(function(){
        postObject[this.name] = this.value;
    });
    return postObject;
}
function formGetObject(form){
    return toUTLParams(formPostObject(form))
}