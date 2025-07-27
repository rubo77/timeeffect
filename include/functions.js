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

// Stop all activities function
function stopAllActivities() {
	if(confirm('Möchten Sie wirklich alle laufenden Aktivitäten stoppen?')) {
		// Create a form and submit it to stop all activities
		var form = document.createElement('form');
		form.method = 'POST';
		form.action = window.location.origin + '/inventory/efforts.php';
		
		var stopAllField = document.createElement('input');
		stopAllField.type = 'hidden';
		stopAllField.name = 'stop_all';
		stopAllField.value = '1';
		form.appendChild(stopAllField);
		
		document.body.appendChild(form);
		form.submit();
	}
}

// Start anything function - navigate to new effort form
function startAnything() {
	// Navigate to the new effort creation form
	window.location.href = window.location.origin + '/inventory/efforts.php?new=1';
}
