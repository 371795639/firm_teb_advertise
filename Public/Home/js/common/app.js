(function($, owner) {
	//封装了页面跳转，以及跳转后所要携带的参数数据extras；
	owner.openwindow = function(url, extras) {
		$.openWindow({
			url: url,
			id: url,
			//preload: true,
			show: {
				aniShow: 'pop-in'
			},
			extras: extras,
			styles: {
				popGesture: 'hide',
				scalable: true
			},
			waiting: {
				autoShow: false
			}
		});
	}
	
}(mui, window.app = {}));
//waiting方法；
var WT = function(msg){
	if(window.plus){
		plus.nativeUI.showWaiting( msg );
	}else{
		document.addEventListener("plusready",plusReady,false);
	}
}
