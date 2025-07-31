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

// Toggle user menu function - placeholder for future user dropdown menu
function toggleUserMenu() {
	// For now, navigate to user settings
	// TODO: Implement dropdown menu with user options
	window.location.href = window.location.origin + '/user/settings.php';
}

/**
 * Validate password strength according to policy:
 * - Minimum 8 characters with at least one number and one special character
 * - OR minimum 12 characters without special requirements
 * 
 * @param {string} password The password to validate
 * @return {object} Object with 'valid' (boolean) and 'message' (string)
 */
function validatePasswordStrength(password) {
	var length = password.length;
	
	if (length < 8) {
		return {
			valid: false,
			message: 'Password must be at least 8 characters long'
		};
	}
	
	// Check for 12+ character rule (no special requirements)
	if (length >= 12) {
		return {
			valid: true,
			message: 'Password meets length requirement'
		};
	}
	
	// Check for 8+ character rule (needs number and special char)
	if (length >= 8) {
		var hasNumber = /[0-9]/.test(password);
		var hasSpecial = /[^a-zA-Z0-9]/.test(password);
		
		if (hasNumber && hasSpecial) {
			return {
				valid: true,
				message: 'Password meets complexity requirements'
			};
		} else {
			var missing = [];
			if (!hasNumber) missing.push('number');
			if (!hasSpecial) missing.push('special character');
			
			return {
				valid: false,
				message: 'Password needs: ' + missing.join(' and ') + ' (or be 12+ characters)'
			};
		}
	}
	
	return {
		valid: false,
		message: 'Password does not meet requirements'
	};
}
