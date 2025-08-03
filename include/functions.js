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
