<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's sqlite3 extension
 * for interacting with SQLite3 databases
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Database
 * @package    DB
 * @author     Bruno Fleisch 
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0 3.0
 * @version    CVS: $Id: sqlite3.php,v 1.3 2007/04/04 14:13:19 bfleisch Exp $
 * @link       http://pear.php.net/package/DB
 */

include_once LIBS.'DB/common.php';



/**
 * The methods PEAR DB uses to interact with PHP's sqlite extension
 * for interacting with SQLite databases
 *
 * These methods overload the ones declared in DB_common.
 *
 */
 
class DB_sqlite3 extends DB_common
{

  // {{{ PROPERTIES
  

  var $phptype = 'sqlite3';
  
  // }}}
  // {{{ constructor
  
  /**
   * constructor
   *
   * @return void
   */
  
  function DB_sqlite3()
  {
    $this->DB_common();
  }
  
  
  // }}}
  // {{{ string errorNative(void)

  /**
   * Gets the DBMS' native error code produced by the last query
   *
   * @return mixed  the DBMS' error code.  A DB_Error object on failure.
   */
  
  function errorNative()
  {
    return sqlite3_error ($this->connection);
  }
  
  // }}}
  // {{{ mixed connect(array $dsn, bool $persitent)
  
  /** 
   * create or connect to the specified database.
   *
   * @param array $dsn         the data source name
   * @param bool  $persitent   if the connection is persitent
   *
   * @return   DB_OK on success or  DB_error object on failure
   */
   
  
  function connect($dsn, $persitent = false)
  {
    $this->connection = sqlite3_open ($dsn['database']);
    if (!$this->connection)
     return $this->raiseError(DB_ERROR_NODBSELECTED);
     
   return DB_OK;
  }
  
  // }}}
  // {{{ bool disconnect (void)
  
    
  /**
   *  release all resources for this connection, and close database
   *
   * @return bool   TRUE on sucess, FALSE otherwise.
   */
  
  function disconnect()
  {
    return sqlite3_close ($this->connection);
  }
  
  // }}}
  // {{{ mixed simpleQuery(string $sql)
  
  /**
   * execute a SQL query.
   *
   * @param string $query  the SQL query 
   *
   * @return mixed + object DB_error object on failure
   *               + object Result resource for SELECT requests
   *               + bool TRUE for other sucessful requests
   */
  
  function simpleQuery($query)
  {
  
    $isSelect = preg_match ("/^\s*SELECT/i", $query); 

    if (! $isSelect)
      $this->result = sqlite3_exec($this->connection, $query);
    else
      $this->result = sqlite3_query($this->connection, $query);
    
    if (!$this->result)
       return $this->RaiseError($this->errorNative());
    
    return $this->result;
  }
  
  // }}}
  // {{{  mixed fetchInto(resource $res, array $arr, int $fetchmode [, int $rownum])

    /**
     * Fetch a row of data into an array which is passed by reference
     *
     * The type of array returned can be controlled either by setting this
     * method's <var>$fetchmode</var> parameter or by changing the default
     * fetch mode setFetchMode() before calling this method.
     *
     * There are two options for standardizing the information returned
     * from databases, ensuring their values are consistent when changing
     * DBMS's.  These portability options can be turned on when creating a
     * new DB object or by using setOption().
     *
     *   + <var>DB_PORTABILITY_LOWERCASE</var>
     *     convert names of fields to lower case
     *
     *   + <var>DB_PORTABILITY_RTRIM</var>
     *     right trim the data
     *
     * @param resource $result     the result resource 
     * @param array    &$arr       the variable where the data should be placed
     * @param int      $fetchmode  the constant indicating how to format the data
     * @param int      $rownum     the row number to fetch (index starts at 0)
     *
     * @return mixed  DB_OK if a row is processed, NULL when the end of the
     *                 result set is reached or a DB_Error object on failure
     *
     * @see DB_common::setOption(), DB_common::setFetchMode()
     */
   
    function fetchInto($result, &$arr, $fetchmode, $rownum = null)
    {
    
      if ($rownum !==NULL)
        return $this->RaiseError (DB_ERROR_NOTIMPLEMENTED);
        
      switch ($fetchmode)
      {
        
        case DB_FETCHMODE_ORDERED:
          $fetchfunc="sqlite3_fetch";
          break;
          
         case DB_FETCHMODE_OBJECT:
          return $this->RaiseError(DB_ERROR_NODBSELECTED);
          break;
          
       case DB_FETCHMODE_ASSOC:
       default:
           $fetchfunc="sqlite3_fetch_array";
          break;
      }
      
      $arr = $fetchfunc($result);
      
      if ($arr) 
        return DB_OK;
      
      return NULL;
    }
    
	// }}}    
  // {{{  string modifyLimitQuery(string $query, int $from, int $count [,mixed $params])
    
    
     /**
     * Adds LIMIT clauses to a query string according to current DBMS standards
     *
     * It is defined here to assure that all implementations
     * have this method defined.
     *
     * @param string $query   the query to modify
     * @param int    $from    the row to start to fetching (0 = the first row)
     * @param int    $count   the numbers of rows to fetch
     * @param mixed  $params  array, string or numeric data to be used in
     *                         execution of the statement.  Quantity of items
     *                         passed must match quantity of placeholders in
     *                         query:  meaning 1 placeholder for non-array
     *                         parameters or 1 placeholder per array element.
     *
     * @return string  the query string with LIMIT clauses added
     *
     * @access protected
     */
     
    function modifyLimitQuery($query, $from, $count, $params = array())
    {
      return "$query LIMIT $count OFFSET $from";
    }
    
    
    // }}} 
    // {{{ bool freeResult(void)
        
    /**
     * free the specified result.
     *
     * @param resource $result   the query resource result
     *
     * @return bool    DB_OK
     *
     */
     
     function freeResult($result)
     {
         sqlite3_query_close($result);
         return DB_OK; /* always sucessful ! */
     }
     
     // }}}
     // {{{ int affectedRow(void)
     
    /**
     * Determines the number of rows affected by a data maniuplation query
     *
     * 0 is returned for queries that don't manipulate data.
     *
     * @return int  the number of rows.  A DB_Error object on failure.
     */
      
     function affectedRows()
     {
       return sqlite3_changes ($this->connection);
     }
     
     // }}}
     // {{{ mixed numCols(resource $result)
     
    /**
     * Get the the number of columns in a result set
     *
     * @return int  the number of columns.  A DB_Error object on failure.
     */
     
     function numCols($result)
     {
	     return sqlite3_column_count($result);
     }
          
     // }}}
     // {{{ mixed createSequence(string $seq_name)
     
     /**
     * Creates a new sequence
     *
     * The name of a given sequence is determined by passing the string
     * provided in the <var>$seq_name</var> argument through PHP's sprintf()
     * function using the value from the <var>seqname_format</var> option as
     * the sprintf()'s format argument.
     *
     * <var>seqname_format</var> is set via setOption().
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::dropSequence(), DB_common::getSequenceName(),
     *      DB_common::nextID()
     */
     
    function createSequence($seq_name)
    {
        return $this->query ("CREATE TABLE  " . $this->getSequenceName($seq_name) . " (id INTEGER PRIMARY KEY AUTOINCREMENT)");
    }
    
    // }}}
    // {{{ mixed nextId(string $sequence [, bool $ondemand])
    
    /**
     * Returns the next free id in a sequence
     *
     * @param string  $seq_name  name of the sequence
     * @param boolean $ondemand  when true, the seqence is automatically
     *                            created if it does not exist
     *
     * @return int  the next id number in the sequence.
     *               A DB_Error object on failure.
     *
     * @see DB_common::createSequence(), DB_common::dropSequence(),
     *      DB_common::getSequenceName()
     */
    function nextId($seq_name, $ondemand = true)
    {
    
      $sqn = $this->getSequenceName($seq_name);
      
      if ($ondemand)
      {
        $tables = $this->getTables();
        if (DB::isError($tables)) return $tables;
        
        if (! in_array ($sqn, $tables))
        {
          $res = $this->createSequence($seq_name);
          if ( DB::isError($res))  
            return $res;           
        }
      }
      
      $res = $this->query ("INSERT INTO " . $sqn . " VALUES (NULL)");
      if (DB::isError($res)) return $res;
      
      return sqlite3_last_insert_rowid ($this->connection);      
    }
    
    // }}}
    // {{{ mixed dropSequence (string $seq_name)
    
     /**
     * Deletes a sequence
     *
     * @param string $seq_name  name of the sequence to be deleted
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::createSequence(), DB_common::getSequenceName(),
     *      DB_common::nextID()
     */
    function dropSequence($seq_name)
    {
        return $this->query("DROP TABLE ". $this->getSequenceName($seq_name));
    }
    
    // }}}
    // {{{ string getSpecialQuery(string $type)
    
     /**
     * Obtains the query string needed for listing a given type of objects
     *
     * @param string $type  the kind of objects you want to retrieve
     *
     * @return string  the SQL query string or null if the driver doesn't
     *                  support the object type requested
     *
     * @access protected
     * @see DB_common::getListOf()
     */
     
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return "SELECT name FROM SQLITE_MASTER ORDER BY name";
            default:
                 return $this->raiseError(DB_ERROR_UNSUPPORTED);
        }
    }
    
    // }}}


}  
?>
