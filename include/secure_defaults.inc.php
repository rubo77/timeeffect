<?php
/**
 * Secure Default Permissions for TimeEffect
 * 
 * This file provides utility functions to apply secure default permissions
 * for new customers and projects created by registered users.
 * 
 * Security Model:
 * - New users can only see their own customers/projects
 * - No access to foreign efforts by default
 * - Restrictive access permissions (owner: rwx, group: r--, others: ---)
 */

/**
 * Apply secure default permissions for new customer/project creation
 * 
 * @param array $data Customer or project data array
 * @param int $user_id ID of the user creating the customer/project
 * @param int $group_id Group ID of the user (optional)
 * @return array Modified data array with secure permissions
 */
function applySecureDefaults($data, $user_id, $group_id = 0) {
    // Check if secure defaults are enabled
    if (!isset($GLOBALS['_PJ_registration_secure_defaults']) || !$GLOBALS['_PJ_registration_secure_defaults']) {
        return $data; // Return unchanged if secure defaults are disabled
    }
    
    // Apply secure access permissions
    $secure_access = isset($GLOBALS['_PJ_registration_default_access']) 
                    ? $GLOBALS['_PJ_registration_default_access'] 
                    : 'rwxr-----'; // Default: owner full access, group read-only, others none
    
    $data['access'] = $secure_access;
    $data['user'] = intval($user_id); // Set current user as owner
    $data['gid'] = intval($group_id);  // Set user's group
    
    // For customers: disable foreign effort reading by default
    if (isset($data['readforeignefforts'])) {
        $data['readforeignefforts'] = 0; // Secure: no foreign efforts visible
    }
    
    return $data;
}

/**
 * Get secure default access permissions for new entities
 * 
 * @return string Access permission string (e.g., 'rwxr-----')
 */
function getSecureDefaultAccess() {
    if (isset($GLOBALS['_PJ_registration_default_access'])) {
        return $GLOBALS['_PJ_registration_default_access'];
    }
    return 'rwxr-----'; // Fallback: owner full access, group read-only, others none
}

/**
 * Check if secure defaults are enabled
 * 
 * @return bool True if secure defaults are enabled
 */
function isSecureDefaultsEnabled() {
    return isset($GLOBALS['_PJ_registration_secure_defaults']) && $GLOBALS['_PJ_registration_secure_defaults'];
}

/**
 * Apply secure defaults specifically for customer creation
 * 
 * @param array $customer_data Customer data array
 * @param int $user_id ID of the user creating the customer
 * @param int $group_id Group ID of the user
 * @return array Modified customer data with secure permissions
 */
function applySecureCustomerDefaults($customer_data, $user_id, $group_id = 0) {
    if (!isSecureDefaultsEnabled()) {
        return $customer_data;
    }
    
    $customer_data = applySecureDefaults($customer_data, $user_id, $group_id);
    
    // Customer-specific secure defaults
    $customer_data['readforeignefforts'] = 0; // Critical: no foreign efforts visible
    $customer_data['active'] = 'yes'; // Default to active
    
    return $customer_data;
}

/**
 * Apply secure defaults specifically for project creation
 * 
 * @param array $project_data Project data array
 * @param int $user_id ID of the user creating the project
 * @param int $group_id Group ID of the user
 * @return array Modified project data with secure permissions
 */
function applySecureProjectDefaults($project_data, $user_id, $group_id = 0) {
    if (!isSecureDefaultsEnabled()) {
        return $project_data;
    }
    
    $project_data = applySecureDefaults($project_data, $user_id, $group_id);
    
    // Project-specific secure defaults
    $project_data['closed'] = 'No'; // Default to open project
    
    return $project_data;
}

/**
 * Log security-related actions for audit purposes
 * 
 * @param string $action Action performed (e.g., 'customer_created', 'project_created')
 * @param int $user_id User ID performing the action
 * @param array $data Data being processed
 */
function logSecurityAction($action, $user_id, $data = array()) {
    if (isSecureDefaultsEnabled()) {
        $log_message = "TimeEffect Security: $action by user $user_id";
        if (!empty($data)) {
            $log_message .= " with secure defaults applied";
        }
        error_log($log_message);
    }
}
?>
