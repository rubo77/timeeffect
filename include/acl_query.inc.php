<?php
/**
 * ACL Query Builder for TimeEffect
 * 
 * This file provides centralized functions to build ACL (Access Control List) queries
 * for user permissions and group access rights. Follows DRY principle to avoid
 * code duplication and SQL syntax errors.
 * 
 * Security Model:
 * - Owner permissions: user field matches current user (rwx------)
 * - Group permissions: gid field matches user's groups (---rwx---)
 * - World permissions: access field allows world access (------rwx)
 */

/**
 * Build ACL WHERE clause for user access permissions
 * 
 * @param object $user User object with giveValue() and checkPermission() methods
 * @param string $tableAlias Optional table alias (e.g., 'te_effort', 'c', etc.)
 * @param string $permission Permission to check ('r' for read, 'w' for write, 'x' for execute)
 * @return string SQL WHERE clause part for ACL filtering
 */
function buildAclWhereClause($user, $tableAlias = '', $permission = 'r') {
    // Admin users bypass all ACL restrictions
    if ($user->checkPermission('admin')) {
        return ''; // No restrictions for admin
    }
    
    $table_prefix = !empty($tableAlias) ? $tableAlias . '.' : '';
    $user_id = $user->giveValue('id');
    $user_gids = $user->giveValue('gids');
    
    // Build permission patterns for different access levels
    $owner_pattern = $permission . '________';     // Owner permission (position 0)
    $group_pattern = '___' . $permission . '_____'; // Group permission (position 3)
    $world_pattern = '______' . $permission . '__'; // World permission (position 6)
    
    $acl_parts = array();
    
    // Owner access: user owns the resource
    $acl_parts[] = "({$table_prefix}user = '$user_id' AND {$table_prefix}access LIKE '$owner_pattern')";
    
    // Group access: user belongs to resource group (only if user has groups)
    if (!empty($user_gids)) {
        $acl_parts[] = "({$table_prefix}gid IN ($user_gids) AND {$table_prefix}access LIKE '$group_pattern')";
    }
    
    // World access: resource allows world access
    $acl_parts[] = "({$table_prefix}access LIKE '$world_pattern')";
    
    return ' AND (' . implode(' OR ', $acl_parts) . ')';
}

/**
 * Build ACL WHERE clause for customer access (legacy compatibility)
 * 
 * @param object $user User object
 * @param string $tableAlias Optional table alias
 * @return string SQL WHERE clause for customer access
 */
function buildCustomerAclQuery($user, $tableAlias = '') {
    return buildAclWhereClause($user, $tableAlias, 'r');
}

/**
 * Build ACL WHERE clause for project access (legacy compatibility)
 * 
 * @param object $user User object
 * @param string $tableAlias Optional table alias
 * @return string SQL WHERE clause for project access
 */
function buildProjectAclQuery($user, $tableAlias = '') {
    return buildAclWhereClause($user, $tableAlias, 'r');
}

/**
 * Build ACL WHERE clause for effort access (legacy compatibility)
 * 
 * @param object $user User object
 * @param string $tableAlias Optional table alias
 * @return string SQL WHERE clause for effort access
 */
function buildEffortAclQuery($user, $tableAlias = '') {
    return buildAclWhereClause($user, $tableAlias, 'r');
}

/**
 * Build raw ACL WHERE clause (without table prefix for simple queries)
 * 
 * @param object $user User object
 * @param string $permission Permission to check ('r', 'w', 'x')
 * @return string SQL WHERE clause for raw queries
 */
function buildRawAclQuery($user, $permission = 'r') {
    return buildAclWhereClause($user, '', $permission);
}

/**
 * Log ACL query for debugging purposes
 * 
 * @param string $context Context description (e.g., 'CustomerList', 'EffortList')
 * @param object $user User object
 * @param string $final_query Final SQL query with ACL applied
 */
function logAclQuery($context, $user, $final_query) {
    $user_id = $user->giveValue('id');
    $is_admin = $user->checkPermission('admin') ? 'YES' : 'NO';
    $gids = $user->giveValue('gids');
    
    debugLog("ACL_DEBUG", "$context: user_id=$user_id, is_admin=$is_admin, gids=$gids");
    debugLog("ACL_DEBUG", "$context final query: $final_query");
}

/**
 * Validate and sanitize user gids for SQL usage
 * 
 * @param string $gids Comma-separated group IDs
 * @return string Sanitized gids or empty string if invalid
 */
function sanitizeUserGids($gids) {
    if (empty($gids)) {
        return '';
    }
    
    // Split by comma and validate each ID is numeric
    $gid_array = explode(',', $gids);
    $clean_gids = array();
    
    foreach ($gid_array as $gid) {
        $gid = trim($gid);
        if (is_numeric($gid) && $gid > 0) {
            $clean_gids[] = intval($gid);
        }
    }
    
    return empty($clean_gids) ? '' : implode(',', $clean_gids);
}

/**
 * Check if user has specific permission on resource
 * 
 * @param object $user User object
 * @param string $access Access string (e.g., 'rwxr--r--')
 * @param int $resource_user_id Owner user ID of the resource
 * @param int $resource_gid Group ID of the resource
 * @param string $permission Permission to check ('r', 'w', 'x')
 * @return bool True if user has permission
 */
function checkUserPermission($user, $access, $resource_user_id, $resource_gid, $permission = 'r') {
    // Admin users have all permissions
    if ($user->checkPermission('admin')) {
        return true;
    }
    
    $user_id = $user->giveValue('id');
    $user_gids = explode(',', $user->giveValue('gids'));
    
    // Check owner permission (position 0, 1, 2)
    if ($resource_user_id == $user_id) {
        $pos = ($permission == 'r') ? 0 : (($permission == 'w') ? 1 : 2);
        return substr($access, $pos, 1) == $permission;
    }
    
    // Check group permission (position 3, 4, 5)
    if (in_array($resource_gid, $user_gids)) {
        $pos = ($permission == 'r') ? 3 : (($permission == 'w') ? 4 : 5);
        return substr($access, $pos, 1) == $permission;
    }
    
    // Check world permission (position 6, 7, 8)
    $pos = ($permission == 'r') ? 6 : (($permission == 'w') ? 7 : 8);
    return substr($access, $pos, 1) == $permission;
}
