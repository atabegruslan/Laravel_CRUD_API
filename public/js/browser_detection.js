var ua = navigator.userAgent.toLowerCase();
var isAndroid = ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
if(isAndroid) {
	$('#android_app').css('visibility', 'visible');
	$('#android_app').css('position', 'relative');
}else{
	$('#android_app').css('visibility', 'hidden');
	$('#android_app').css('position', 'absolute');
}