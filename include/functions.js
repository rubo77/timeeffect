function openPopUp(url, width, height) {
	if(width == 0) {
		width = 400;
	}
	if(height == 0) {
		height = 200;
	}

	var RANDOM = new String (Math.random());	// random string
	RANDOM = RANDOM.replace(/0\./,"");
	RANDOM = RANDOM.substr(0, 8);

	win = window.open(url, "popup_" + RANDOM, "width=" + width + ",height=" + height + ",scrollbars=yes,status=yes");
}
