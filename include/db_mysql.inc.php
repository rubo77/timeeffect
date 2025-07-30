<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id$
 *
 */ 

class DB_Sql {
  
  /* public: connection parameters */
  var $Host;
  var $Database;
  var $User;
  var $Password;

  /* public: configuration parameters */
  var $Auto_Free     = 1;     ## Set to 1 for automatic mysqli_free_result()
  var $Debug         = 0;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
  var $Seq_Table     = "db_sequence";

  /* public: result array and current row number */
  var $Record   = array();
  var $Row;

  /* public: current error number and error text */
  var $Errno    = 0;
  var $Error    = "";

  /* public: this is an api revision, not a CVS revision. */
  var $type     = "mysqli";
  var $revision = "1.2";

  /* private: link and query handles */
  var $Link_ID  = 0;
  var $Query_ID = 0;
  
  
  
  /* public: constructor */
  function __construct($query = "") {
    $this->query($query);
  }
  
  /* public: constructor */
  function DB_Sql($query = "") {
    self::__construct($query);
  }

  /* public: some trivial reporting */
  function link_id() {
    return $this->Link_ID;
  }

  function query_id() {
    return $this->Query_ID;
  }

  /* public: connection management */
  function connect($Database = "", $Host = "", $User = "", $Password = "") {
    /* Handle defaults */
    if ("" == $Database)
      $Database = $this->Database;
    if ("" == $Host)
      $Host     = $this->Host;
    if ("" == $User)
      $User     = $this->User;
    if ("" == $Password)
      $Password = $this->Password;
      
    /* establish connection, select database */
    if ( empty($this->Link_ID) ) {
    
      // Try to get config values from GLOBALS if available (used by Database class)
      if (empty($Host) && isset($GLOBALS['_PJ_db_host'])) {
        $Host = $GLOBALS['_PJ_db_host'];
      }
      
      if (empty($Database) && isset($GLOBALS['_PJ_db_database'])) {
        $Database = $GLOBALS['_PJ_db_database'];
      }
      
      if (empty($User) && isset($GLOBALS['_PJ_db_user'])) {
        $User = $GLOBALS['_PJ_db_user'];
      }
      
      if (empty($Password) && isset($GLOBALS['_PJ_db_password'])) {
        $Password = $GLOBALS['_PJ_db_password'];
      }
      
      // if host if empty or mysql/localhost (Docker environment)
      if (empty($Host) || $Host == 'mysql' || $Host == 'localhost') {
        debugLog("HINT", "this is a problem only on docker: Host was empty or 'mysql' in DSN, should be set to 'db'");
      }
      
      // if user is empty
      if (empty($User)) {
        die('no database user given');
      }
      
      // if database is empty
      if (empty($Database)) {
        die('no database name given');
      }
      
      $this->Link_ID=@mysqli_connect($Host, $User, $Password);
      if (!$this->Link_ID) {
        $this->Halt_On_Error="report";
        $this->halt("connect($Host, $User, \$Password) failed.");
        return 0;
      }

      if (!@mysqli_select_db($this->Link_ID, $Database)) {
        $this->halt("cannot use database ".$this->Database);
        return 0;
      }
      
      // Set MySQL charset with fallback to utf8
      $charset = isset($GLOBALS['mysql_charset']) ? $GLOBALS['mysql_charset'] : 'utf8';
      if (!empty($charset)) {
        mysqli_set_charset($this->Link_ID, $charset);
      }
    }

    return $this->Link_ID;
  }

  /* public: discard the query result */
  function free() {
      if ($this->Query_ID && is_object($this->Query_ID)) {
          @mysqli_free_result($this->Query_ID);
      }
      $this->Query_ID = 0;
  }

  /* public: perform a query */
  function query($Query_String) {
    /* No empty queries, please, since PHP4 chokes on them. */
    if ($Query_String == "")
      /* The empty query string is passed on from the constructor,
       * when calling the class without a query, e.g. in situations
       * like these: '$db = new DB_Sql_Subclass;'
       */
      return 0;

    if (!$this->connect()) {
      return 0; /* we already complained in connect() about that. */
    };

    # New query, discard previous result.
    if ($this->Query_ID) {
      $this->free();
    }

    if ($this->Debug)
      printf("Debug: query = %s<br>\n", $Query_String);

    $this->Query_ID = @mysqli_query($this->Link_ID, $Query_String);
    $this->Row   = 0;
    $this->Errno = mysqli_errno($this->Link_ID);
    $this->Error = mysqli_error($this->Link_ID);
    if (!$this->Query_ID) {
      $this->halt("Invalid SQL: ".$Query_String);
    }

    # Will return nada if it fails. That's fine.
    return $this->Query_ID;
  }

  /* public: walk result set */
  function next_record() {
    if (!$this->Query_ID) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysqli_fetch_array($this->Query_ID);
    $this->Row   += 1;
    $this->Errno  = mysqli_errno($this->Link_ID);
    $this->Error  = mysqli_error($this->Link_ID);

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $stat;
  }

  /* public: position in result set */
  function seek($pos = 0) {
    $status = @mysqli_data_seek($this->Query_ID, $pos);
    if(!empty($status))
      $this->Row = $pos;
    else {
      $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");

      /* half assed attempt to save the day, 
       * but do not consider this documented or even
       * desireable behaviour.
       */
      @mysqli_data_seek($this->Query_ID, $this->num_rows());
      $this->Row = $this->num_rows;
      return 0;
    }

    return 1;
  }

  /* public: table locking */
  function lock($table, $mode="write") {
    $this->connect();
    
    $query="lock tables ";
    if (is_array($table)) {
      foreach ($table as $key => $value) {
        if ($key=="read" && $key!=0) {
          $query.="$value read, ";
        } else {
          $query.="$value $mode, ";
        }
      }
      $query=substr($query,0,-2);
    } else {
      $query.="$table $mode";
    }
    $res = @mysqli_query($this->Link_ID, $query);
    if(empty($res)) {
      $this->halt("lock($table, $mode) failed.");
      return 0;
    }
    return $res;
  }
  
  function unlock() {
    $this->connect();

    $res = @mysqli_query($this->Link_ID, "unlock tables");
    if(empty($res)) {
      $this->halt("unlock() failed.");
      return 0;
    }
    return $res;
  }


  /* public: evaluate the result (size, width) */
  function affected_rows() {
    return @mysqli_affected_rows($this->Link_ID);
  }

  /* public: evaluate auto_increment */
  function insert_id() {
    return @mysqli_insert_id($this->Link_ID);
  }

  function num_rows() {
    return @mysqli_num_rows($this->Query_ID);
  }

  function num_fields() {
    return @mysqli_num_fields($this->Query_ID);
  }

  /* public: shorthand notation */
  function nf() {
    return $this->num_rows();
  }

  function np() {
    print $this->num_rows();
  }

  function f($Name) {
    return $this->Record[$Name];
  }

  function p($Name) {
    print $this->Record[$Name];
  }

  /* public: sequence numbers */
  function nextid($seq_name) {
    $this->connect();
    
    if ($this->lock($this->Seq_Table)) {
      /* get sequence number (locked) and increment */
      $q  = sprintf("select nextid from %s where seq_name = '%s'",
                $this->Seq_Table,
                $seq_name);
      $id  = @mysqli_query($this->Link_ID, $q);
      $res = @mysqli_fetch_array($id);
      
      /* No current value, make one */
      if (!is_array($res)) {
        $currentid = 0;
        $q = sprintf("insert into %s values('%s', %s)",
                 $this->Seq_Table,
                 $seq_name,
                 $currentid);
        $id = @mysqli_query($this->Link_ID, $q);
      } else {
        $currentid = $res["nextid"];
      }
      $nextid = $currentid + 1;
      $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
               $this->Seq_Table,
               $nextid,
               $seq_name);
      $id = @mysqli_query($this->Link_ID, $q);
      $this->unlock();
    } else {
      $this->halt("cannot lock ".$this->Seq_Table." - has it been created?");
      return 0;
    }
    return $nextid;
  }

  /* public: return table metadata */
  function metadata($table='',$full=false) {
    $count = 0;
    $id    = 0;
    $res   = array();

    /*
     * Due to compatibility problems with Table we changed the behavior
     * of metadata();
     * depending on $full, metadata returns the following values:
     *
     * - full is false (default):
     * $result[]:
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *
     * - full is true
     * $result[]:
     *   ["num_fields"] number of metadata records
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *   ["meta"][field name]  index of field named "field name"
     *   The last one is used, if you have a field name, but no index.
     *   Test:  if (isset($result['meta']['myfield'])) { ...
     */

    // if no $table specified, assume that we are working with a query
    // result
    if(!empty($table)) {
      $this->connect();
      $id = @mysqli_query($this->Link_ID, "SHOW COLUMNS FROM $table");
      if(empty($id))
        $this->halt("Metadata query failed.");
    } else {
      $id = $this->Query_ID; 
      if(empty($id))
        $this->halt("No query specified.");
    }
 
    $count = @mysqli_num_fields($id);

    // made this IF due to performance (one if is faster than $count if's)
    if(empty($full)) {
      for ($i=0; $i<$count; $i++) {
        $field_info = @mysqli_fetch_field_direct($id, $i);
        $res[$i]["table"] = $field_info->table ?? '';
        $res[$i]["name"]  = $field_info->name ?? '';
        $res[$i]["type"]  = $field_info->type ?? '';
        $res[$i]["len"]   = $field_info->length ?? 0;
        $res[$i]["flags"] = $field_info->flags ?? '';
      }
    } else { // full
      $res["num_fields"]= $count;
    
      for ($i=0; $i<$count; $i++) {
        $field_info = @mysqli_fetch_field_direct($id, $i);
        $res[$i]["table"] = $field_info->table ?? '';
        $res[$i]["name"]  = $field_info->name ?? '';
        $res[$i]["type"]  = $field_info->type ?? '';
        $res[$i]["len"]   = $field_info->length ?? 0;
        $res[$i]["flags"] = $field_info->flags ?? '';
        $res["meta"][$res[$i]["name"]] = $i;
      }
    }
    
    // free the result only if we were called on a table
    if(!empty($table)) @mysqli_free_result($id);
    return $res;
  }

  /* private: error handling */
  function halt($msg) {
    $this->Error = @mysqli_error($this->Link_ID);
    $this->Errno = @mysqli_errno($this->Link_ID);
    if ($this->Halt_On_Error == "no")
      return;

    $this->haltmsg($msg);

    if ($this->Halt_On_Error != "report")
      die("Session halted.");
  }

  function haltmsg($msg) {
    printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
    printf("<b>MySQL Error</b>: %s (%s)<br>\n",
      $this->Errno,
      $this->Error);
  }

  function table_names() {
    $this->query("SHOW TABLES");
    $i=0;
    while ($info=mysqli_fetch_row($this->Query_ID))
     {
      $return[$i]["table_name"]= $info[0];
      $return[$i]["tablespace_name"]=$this->Database;
      $return[$i]["database"]=$this->Database;
      $i++;
     }
   return $return;
  }
}
?>
