<?php
/**
 * Group Assignment Methods - Extension for Group class
 * Provides methods to find users and objects assigned to a group
 */

/**
 * Simple helper function to safely get values from DB records
 * @param array $record Database record
 * @param string $key Field name
 * @return string Safe value or empty string
 */
function getRecordValue($record, $key) {
    return isset($record[$key]) ? $record[$key] : '';
}

// Extend Group class with assignment methods
if (class_exists('Group')) {
    
    /**
     * Get all users assigned to this group
     * @return array Array of user records
     */
    function Group_getAssignedUsers($group) {
        $group->db->connect(); // Ensure database connection
        $group_id = DatabaseSecurity::escapeString($group->data['id'], $group->db->Link_ID);
        
        $query = sprintf("SELECT * FROM %s WHERE FIND_IN_SET('%s', gids) > 0 ORDER BY lastname, firstname",
            $GLOBALS['_PJ_auth_table'],
            $group_id
        );
        
        $group->db->query($query);
        $users = array();
        
        while($group->db->next_record()) {
            $users[] = $group->db->Record; // Direct DB record - no object creation needed
        }
        
        return $users;
    }
    
    /**
     * Get all customers assigned to this group
     * @return array Array of customer records
     */
    function Group_getAssignedCustomers($group) {
        $group->db->connect(); // Ensure database connection
        $group_id = DatabaseSecurity::escapeString($group->data['id'], $group->db->Link_ID);
        
        $query = sprintf("SELECT * FROM %s WHERE FIND_IN_SET('%s', gid) > 0 ORDER BY customer_name",
            $GLOBALS['_PJ_customer_table'],
            $group_id
        );
        
        $group->db->query($query);
        $customers = array();
        
        while($group->db->next_record()) {
            $customers[] = $group->db->Record; // Direct DB record - clean and simple
        }
        
        return $customers;
    }
    
    /**
     * Get all projects assigned to this group
     * @return array Array of project records
     */
    function Group_getAssignedProjects($group) {
        $group->db->connect(); // Ensure database connection
        $group_id = DatabaseSecurity::escapeString($group->data['id'], $group->db->Link_ID);
        
        $query = sprintf("SELECT * FROM %s WHERE FIND_IN_SET('%s', gid) > 0 ORDER BY project_name",
            $GLOBALS['_PJ_project_table'],
            $group_id
        );
        
        $group->db->query($query);
        $projects = array();
        
        while($group->db->next_record()) {
            $projects[] = $group->db->Record; // Direct DB record - elegant solution
        }
        
        return $projects;
    }
    
    /**
     * Get all efforts assigned to this group
     * @return array Array of effort records
     */
    function Group_getAssignedEfforts($group) {
        $group->db->connect(); // Ensure database connection
        $group_id = DatabaseSecurity::escapeString($group->data['id'], $group->db->Link_ID);
        
        $query = sprintf("SELECT * FROM %s WHERE FIND_IN_SET('%s', gid) > 0 ORDER BY begin DESC LIMIT 50",
            $GLOBALS['_PJ_effort_table'],
            $group_id
        );
        
        $group->db->query($query);
        $efforts = array();
        
        while($group->db->next_record()) {
            $efforts[] = $group->db->Record; // Direct DB record - no unnecessary object creation
        }
        
        return $efforts;
    }
    
    /**
     * Get count of all objects assigned to this group
     * @return array Associative array with counts
     */
    function Group_getAssignmentCounts($group) {
        $group->db->connect(); // Ensure database connection
        $group_id = DatabaseSecurity::escapeString($group->data['id'], $group->db->Link_ID);
        
        $counts = array(
            'users' => 0,
            'customers' => 0,
            'projects' => 0,
            'efforts' => 0
        );
        
        // Count users
        $query = sprintf("SELECT COUNT(*) as count FROM %s WHERE FIND_IN_SET('%s', gids) > 0",
            $GLOBALS['_PJ_auth_table'], $group_id);
        $group->db->query($query);
        if($group->db->next_record()) {
            $counts['users'] = intval($group->db->Record['count']);
        }
        
        // Count customers
        $query = sprintf("SELECT COUNT(*) as count FROM %s WHERE FIND_IN_SET('%s', gid) > 0",
            $GLOBALS['_PJ_customer_table'], $group_id);
        $group->db->query($query);
        if($group->db->next_record()) {
            $counts['customers'] = intval($group->db->Record['count']);
        }
        
        // Count projects
        $query = sprintf("SELECT COUNT(*) as count FROM %s WHERE FIND_IN_SET('%s', gid) > 0",
            $GLOBALS['_PJ_project_table'], $group_id);
        $group->db->query($query);
        if($group->db->next_record()) {
            $counts['projects'] = intval($group->db->Record['count']);
        }
        
        // Count efforts
        $query = sprintf("SELECT COUNT(*) as count FROM %s WHERE FIND_IN_SET('%s', gid) > 0",
            $GLOBALS['_PJ_effort_table'], $group_id);
        $group->db->query($query);
        if($group->db->next_record()) {
            $counts['efforts'] = intval($group->db->Record['count']);
        }
        
        return $counts;
    }
}
