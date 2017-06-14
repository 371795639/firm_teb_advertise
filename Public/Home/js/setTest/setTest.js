mui.init();
    		//表单验证；
$("#btn").attr("disabled","disabled").removeClass("gradient-btn").css("background-color","#999");
var test = function(){
	var oldPsd = $("#oldPassword").val();
	var newPsd = $("#newPassword").val();
	var psd = $("#password").val();
//	console.log(oldPsd+newPsd+psd);
	var isOk = oldPsd&&newPsd&&psd;
	btn( isOk );
}

var btn = function(a){
	if( a ){
     	$("#btn").removeAttr("disabled").addClass("gradient-btn");
    }else{
     	$("#btn").attr("disabled","disabled").removeClass("gradient-btn").css("background-color","#999");
    }
}
$('input').bind('keyup',function(){
	//验证空值；
	test();			
});
//验证两次密码是否一样
$("#password").blur(function(){
	//验证两次输入密码；
	testPsd();
});	 	
var testPsd = function(){
	var val3 = $("#newPassword").val();
	var val4 = $("#password").val();
	if( val3==val4 ){
		return null;
	}else{
		mui.toast("两次密码不一致！");
		$("#newPassword").val("");
		$("#password").val("");	
		btn(0);
	}
}
//显示密码功能；
var showOldPsd = function(){set( oldPassword );}
var showNewPsd = function(){set( newPassword );}
var showPsd = function(){set( password );}
//方法
var set = function( id ){//对应input id
	if( $(id).attr("type") == "password" ){
		$(id).attr('type','text');
	}else{
		$(id).attr('type','password');
	}
} 
			