<?
require_once("exceptions/exceptions_main.php");

class Database 
{
	
	// local vars
	protected $hostname;				// string
	protected $username;				// string
	protected $password;				// string
	protected $database;				// string
	protected $table_name;				// string
	
	protected $affected_rows_count;		// integer
	protected $error_message_header;	// string
	protected $last_inserted_id;		// integer
	protected $message_header;			// string
	protected $mysqli;					// object
	
	function __construct($db_connection=NULL)
	{
		// local vars
		$error_message;
		$error_message_header;
		
		// set local message header
		$message_header = "ERROR MESSAGE: The following class '<b>".strtoupper(get_class($this))."</b>' encountered an error.";
		
		if($db_connection) {
		  $this->hostname = 	$db_connection["hostname"];
		  $this->username = 	$db_connection["username"];
		  $this->password = 	$db_connection["password"];
		  $this->database = 	$db_connection["database"];
		  $this->table_name = 	$db_connection["table_name"];
		}
		else {
			// set and display error message, then exit
			$error_message = "<p>".$message_header." The function '__construct()' requires database connection parameter values.</p>";
			echo($error_message);
			exit();
		}
		
		// set global error message header
		$this->message_header = "MESSAGE: The following class '<b>".strtoupper(get_class($this))."</b>' utilized by the class table '".$this->table_name."' has a message.";
		$this->error_message_header = "ERROR MESSAGE: The following class '<b>".strtoupper(get_class($this))."</b>' utilized by the class table '".$this->table_name."' encountered an error.";
		
		// set global database connection
		$this->set_database_connection();
	}
	
	// CORE FUNCTIONS----------------------------------------------------------
	
	protected function connect_to_database()
	{
		// check if database connection already exists
		// if it exists, then validate connection status
		// else set database connection
		if($this->mysqli) {
			// check if server is alive
			// if server is alive, do nothing as our connection status is okay
			// else re-set database connection
			if($this->mysqli->ping()) {
				// do nothing, our connection is good
				//echo("<p>".$this->message_header." Our connection is ok!</p>");
			}
			else {
				// re-set database connection
				$this->set_database_connection();
				//echo("<p>".$this->message_header." An EXISTING database connection was just RE-created!</p>");
			}
		}
		else {
			// set database connection
			$this->set_database_connection();
			//echo("<p>".$this->message_header." A NEW database connection was just created!</p>");
		}
	}
	
	function get_affected_rows_count()
	{
		// return affected rows count
		return $this->affected_rows_count;
	}
	
	function get_last_inserted_id()
	{
		// return last inserted id
		return $this->last_inserted_id;
	}
	
	protected function multi_query($sql_commands, $query_type=NULL)
	{
		// local vars
		$error_message;
		$result;
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// validate param query type
		// if null, then set query type to 'not provided'
		if($query_type == NULL) {
			$query_type = "not provided";
		}
		
		// connect to databae
		$this->connect_to_database();
		
		// starts transaction
		// not working...
		// switch autocommit status to FALSE.
		//$this->mysqli->autocommit(FALSE);
		
		// send query to database
		$result = $this->mysqli->multi_query($sql_commands);
		
		// check to see if there is a result
		// if result exists, then set vars last inserted id and affected rows count
		// else, display error message and rollback transactions
		if($result) {
			// not working...
			// commit transactions
			//$this->mysqli->commit();
			
			// set last inserted id
			// if insert id exists, then set var last inserted id to insert id
			// else, set var last inserted id to null
			if($last_inserted_id = $this->mysqli->insert_id) {
				$this->last_inserted_id = $last_inserted_id;
			}
			else {
				$this->last_inserted_id = NULL;
			}
			//echo("<p>The last inserted id: '".$this->last_inserted_id."'</p>");
			
			// set affected rows count
			// if affected rows exists, then set var affected rows count to affected rows
			// else, set var affected rows count to null
			if($affected_rows_count = $this->mysqli->affected_rows) {
				$this->affected_rows_count = $affected_rows_count;
			}
			else {
				$this->affected_rows_count = NULL;
			}
			//echo("<p>The number of affected rows count: '".$this->affected_rows_count."'</p>");
		}
		else {
			// set and display error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Mulit-query command failed: (".$this->mysqli->errno.") ".$this->mysqli->error.", query type: '".$query_type."', sql_command_queries: '".$sql_commands."'.";
			//echo("<p>".$error_message."</p>");
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $query_array=NULL, $sql_commands, $result);
			
			// not working...
			// rollback transactions
		 	//$this->mysqli->rollback();
		}
		
		// not working...
		// ends transaction
		// switch back autocommit status
		//$this->mysqli->autocommit(TRUE);
		
		// return result
		return $result;
	}
	
	protected function set_database_connection()
	{
		// local vars
		$error_message;
		$mysqli;
		
		// connect to and set database
		$mysqli = new MySQLi($this->hostname, $this->username, $this->password, $this->database);
		
		if($mysqli->connect_errno) {
			// set and display error message, then exit
			$error_message = "<p>".$this->error_message_header." Failed to connect to MySQL: (".$mysqli->connect_errno.") ".$mysqli->connect_error."</p>";
			echo($error_message);
			exit();
		}
		else {
			$this->mysqli = $mysqli;
		}
	}
	
	protected function single_query($sql_command, $query_type=NULL)
	{
		// local vars
		$error_message;
		$result = TRUE;
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// validate param query type
		// if null, then set query type to 'not provided'
		if($query_type == NULL) {
			$query_type = "not provided";
		}
		
		// connect to databae
		$this->connect_to_database();
		
		// send query
		$result = $this->mysqli->query($sql_command);
		
		// check to see if there is a result
		// if result exists, then set vars last inserted id and affected rows count
		// else, display error message
		if($result) {
			// set last inserted id
			// if insert id exists, then set var last inserted id to insert id
			// else, set var last inserted id to null
			if($last_inserted_id = $this->mysqli->insert_id) {
				$this->last_inserted_id = $last_inserted_id;
			}
			else {
				$this->last_inserted_id = NULL;
			}
			//echo("<p>The last inserted id: '".$this->last_inserted_id."'</p>");
			
			// set affected rows count
			// if affected rows exists, then set var affected rows count to affected rows
			// else, set var affected rows count to null
			if($affected_rows_count = $this->mysqli->affected_rows) {
				$this->affected_rows_count = $affected_rows_count;
			}
			else {
				$this->affected_rows_count = NULL;
			}
			//echo("<p>The number of affected rows count: '".$this->affected_rows_count."'</p>");
		}
		else {
			// set and display error message
			$error_message = $this->error_message_header."The function '<b>".$function_name."()</b>' had an error. Single-query command failed: (".$this->mysqli->errno.") ".$this->mysqli->error.", query type: '".$query_type."', sql_command_queries: '".$sql_command."'.";
			//echo("<p>".$error_message."</p>");
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $query_array=NULL, $sql_command, $result);
		}
		
		// optional
		// close database connection
		//$this->mysqli->close();
		
		// return result
		return $result;
	}
	
	function quote($value)
	{
		// local vars
		$result;
		$value;
		
		// connect to databae
		$this->connect_to_database();
		
		$value = $this->mysqli->real_escape_string($value);
		
		// quote value and set result
		$result = "\"".$value."\"";
		
		// optional
		// close database connection
		//$this->mysqli->close();
		
		// return result
		return $result;
	}
	
	// CLASS FUNCTIONS ---------------------------------------------------------
	
	function multi_query_requests($sql_requests, $strip_slashes=TRUE)
	{
		// local vars
		$counter;
		$data_fields;		// (array), processed data from extracted data
		$data_recieved;		// (object), sql query result
		$data_row;			// (array), extracted data from sql query result
		$query_type;		// (string), type of query (i.e. request, statement, other, etc)
		$r1;				// (array), temporary holding array
		$result;			// (multi-level array), resulting data, collection of processed data
		$sql_request_pieces;
		$temp_data_array;
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// set query type
		$query_type = "request";
		
		// put each query request in its own array
		$sql_request_pieces = explode(";", $sql_requests);
		//echo "<pre>"; print_r($sql_request_pieces); echo "</pre><br>";
		
		// get query count
		$query_count = count($sql_request_pieces);
		
		// if last array is an empty string 
		// then drop array and decrement query count
		if($sql_request_pieces[$query_count-1] == ""){
			unset($sql_request_pieces[--$query_count]);
		}
		
		// send query to database and get results
		try {
			$data_recieved = $this->multi_query($sql_requests, $query_type);
		}
		catch(FailedQueryException $ex) {			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' recieved the following message: ".$ex->get_errorMessage();
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $ex->my_trace, $sql_request_pieces, $ex->sql, $ex->result);
		}
		
		// send multi sql query
		// if data recieved, then extract any data and set to result
		// else, set result to data recieved (i.e. false)
		if($data_recieved) {
			// initialize counter, result and r1
			$counter = 0;
			$result = array();
			$r1;
			
			// extract data recieved into an array and set to result
			do {
				if($data_row = $this->mysqli->store_result()) {
					
					// initialize data field
					$data_fields = array();
					$temp_data_array = array();
					
					// get a row of data
					while($r1 = $data_row->fetch_array(MYSQLI_ASSOC)) {
						//set data row 'r1' to the temporary array
						$temp_data_array[] = $r1;
					}
					
					// free data row
					$data_row->free();
					
					// get array size of temporary array
					$array_size = count($temp_data_array);
					
					// if array size is greater than one, then set result to the temporary array
					// else, set result to single inner array in temporary array
					if($array_size > 1) {
						$result[] = $temp_data_array;
					}
					else {
						$result[] = $temp_data_array[0];
					}
				}
				
				// increment counter
				$counter++;
			}
			while($this->mysqli->more_results() && $this->mysqli->next_result());
			
			// check for errors
			// if there is an error, display error message and set result to false
			if($this->mysqli->errno) {
				// set values for error message
				// set index with current value of counter, for use with array sql_request_pieces
				// then increase counter to match var query_count. we started counter on zero (to match with array sql_request_pieces's index), rather than 1.
				$index = $counter++;
				
				// set and display error message
				$error_message = $this->error_message_header." Batch query function '<b>".$function_name."()</b>' prematurely ended on database query statement '".$sql_request_pieces[$index]."' [array index #".$index." or #".$counter." of ".$query_count." queries]. Query command failed: (".$this->mysqli->errno.") ".$this->mysqli->error.".";
				
				// thow an exception for the query command error
				throw new BadQueryException($error_message, $my_trace=NULL, $sql_request_pieces, $sql_requests, $result);
			}
			
			// if result and strip slashes is true, then strip slashes from the value strings in the data array, then set to result
			if($result && $strip_slashes) {
				// remove encoded slashes from each value in array result
				$result = $this->stripslashes_from_values($result);
			}
		}
		else {
			// set result to data recieved
			$result = $data_recieved;
			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Batch query return <b>failed</b>. Please check your SQL queries.";
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $sql_request_pieces, $sql_requests, $result);
		}
			
		// optional
		// close database connection
		//$this->mysqli->close();
		//echo "<pre>"; print_r($result); echo "</pre><br>";
		
		// return result
		return $result;
	}
	
	function multi_query_statements($sql_statements)
	{
		// local vars
		$counter;
		$data_fields;		// (array), processed data from extracted data
		$data_recieved;		// (object), sql query result
		$data_row;			// (array), extracted data from sql query result
		$query_type;		// (string), type of query (i.e. request, statement, other, etc)
		$r1;				// (array), temporary holding array
		$result;			// (multi-level array), resulting data, collection of processed data
		$sql_statement_pieces;
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// set query type
		$query_type = "statement";
		
		// put each query statement in its own array
		$sql_statement_pieces = explode(";", $sql_statements);
		//echo "<pre>"; print_r($sql_statement_pieces); echo "</pre><br>";
		
		// get query count
		$query_count = count($sql_statement_pieces);
		
		// if last array is an empty string 
		// then drop array and decrement query count
		if($sql_statement_pieces[$query_count-1] == "") {
			unset($sql_statement_pieces[--$query_count]);
		}
		//echo "<pre>"; print_r($sql_statement_pieces); echo "</pre><br>";
		
		// send query to database and get results
		try {
			$data_recieved = $this->multi_query($sql_statements, $query_type);
		}
		catch(FailedQueryException $ex) {			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' recieved the following message: ".$ex->get_errorMessage();
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $ex->my_trace, $sql_statement_pieces, $ex->sql, $ex->result);
		}
		
		// send multi sql query
		// if data recieved, then verify if all queries completed, display error if incomplete
		// else, set result to data recieved (i.e. false)
		if($data_recieved) {
			// initialize counter, result and r1
			$counter = 1;
			$result = $data_recieved;
			$r1 = array();
			
			// do nothing except, iterate (flush) through recieved results and increment counter
			while($this->mysqli->more_results() && $this->mysqli->next_result()){
				// increment counter
				$counter++;
			}
			
			// check for error
			// if error, then display error message and set result to false
			if($this->mysqli->errno){
				// set values for error message
				// set index with current value of counter, for use with array sql_request_pieces
				// then increase counter to match var query_count. we started counter on zero (to match with array sql_request_pieces's index), rather than 1.
				$index = $counter++;
				
				// set and display error message
				$error_message = $this->error_message_header." Batch query function '<b>".$function_name."()</b>' prematurely ended on database query statement '".$sql_statement_pieces[$index]."' [array index #".$index." or #".$counter." of ".$query_count." queries]. Query command failed: (".$this->mysqli->errno.") ".$this->mysqli->error.".";
				
				// thow an exception for the query command error
				throw new BadQueryException($error_message, $my_trace=NULL, $sql_statement_pieces, $sql_statements, $result);
			}
		}
		else {
			// set result to data recieved
			$result = $data_recieved;
			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Batch query return <b>failed</b>. Please check your SQL queries.";
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $sql_statement_pieces, $sql_statements, $result);
		}
		
		// optional
		// close database connection
		//$this->mysqli->close();
		//echo "<pre>"; print_r($result); echo "</pre><br>";
		
		// return result
		return $result;
	}
	
	function stripslashes_from_values($val_array)
	{
		// local var
		$modified_array;
		$result;
		
		// strip the slashes from each string value in the val array
		foreach($val_array as $field_name=>$value) {
			// check to see if the value is an array
			// if it is an array, then recursively call 'this' function on the new array and and add returned array to the modified array
			// else, stripslashes from the string value and add to the modified array
			if(is_array($value)) {
				$modified_array[$field_name] = $this->stripslashes_from_values($value);
			}
			else {
				// remove encoded slashes from each value in the data row and set to data fields
				$modified_array[$field_name] = stripslashes($value);
			}
		}
		
		// set result to modified array
		$result = $modified_array;
		
		// return result
		return $result;
	}
	
	function table_exists($table_name)
	{
		// local vars
		$data_recieved;
		$function_name;
		$query_type;
		$result;
		$sql_request;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// set query type
		$query_type = "table exists request";
		
		// set query
		$sql_request = "SHOW TABLES LIKE ".$this->quote($table_name);
		
		// send query to database and get results
		try {
			$data_recieved = $this->single_query($sql_request, $query_type);
		}
		catch(FailedQueryException $ex) {			
			// set error message
			$error_message = "The function '<b>".$function_name."()</b>' recieved the following message: ".$ex->get_errorMessage();
			
			// display error message
			echo("<p>".$error_message."</p>");
			
			// display error data (i.e. query)
			$ex->display_error_data();
			
			// exit application
			exit();
		}
		
		// verify if data recieved
		// if data recieved, then set result to number of rows returned
		// else, display error message and exit application
		if($data_recieved) {
			// set result with number of rows returned
			$result = $data_recieved->num_rows;
		}
		else {
			// set and display error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Single query return <b>failed</b>. Please check your SQL query.";
			echo("<p>".$error_message."</p>");
			
			// display query
			if($sql_request) {
				echo("<p>Query attempted follows: </p>");
				echo "<pre>"; print_r($sql_request); echo "</pre><br>";
			}
			
			// exit application
			exit();
		}
		
		// optional
		// close connection
		//$this->mysqli->close();
		
		// return result
		return $result;
	}
	
	// function: retrieves and returns results from a SQL query
	// params: $sql_query (string), SQL query
	// returns: $result (array), returned data from SQL database query
	function query_request($sql_request, $strip_slashes=TRUE)
	{
		// local vars
		$data_fields;		// (array), processed data from extracted data
		$data_recieved;		// (object), sql query result
		$data_row;			// (array), extracted data from sql query result
		$i;					// (int), counter
		$num_row;			// (int), number of returned rows
		$query_type;		// (string), type of query (i.e. request, statement, other, etc)
		$result;			// (multi-level array), resulting data, collection of processed data
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// set request type
		$query_type = "request";
		
		// send query to database and get results
		try {
			$data_recieved = $this->single_query($sql_request, $query_type);
		}
		catch(FailedQueryException $ex) {			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' recieved the following message: ".$ex->get_errorMessage();
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $ex->my_trace, $ex->query_array, $ex->sql, $ex->result);
		}
		
		// if data recieved, extract any data and set to result
		// else, set result to data recieved
		if($data_recieved) {
			//initialize result
			$result = array();
			
			// get number of data rows recieved
			$num_row = $data_recieved->num_rows;
			
			// extract data recieved into an array and set to result
			for($i=0; $i<$num_row; $i++) {
				// initialize data field
				$data_fields = array();
				
				// get a row of data
				$data_row = $data_recieved->fetch_assoc();
				
				// add data fields to result set
				$result[] = $data_row;
			}
			
			// if true, then strip slashes from the value strings in the data array, then set to result
			if($result && $strip_slashes) {
				// remove encoded slashes from each value in array result
				$result = $this->stripslashes_from_values($result);
			}
		}
		else {
			// set result
			$result = $data_recieved;
			
			// set and display error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Single query return <b>failed</b>. Please check your SQL query.";
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $query_array=NULL, $sql_request, $result);
		}
		
		// optional
		// close connection
		//$this->mysqli->close();
		//echo "<pre>"; print_r($result); echo "</pre><br>";
		
		// return result
		return $result;
	}
	
	// function: retrieves and returns results from a SQL query
	// params: $sql_query (string), SQL query
	// returns: $result (array), returned data from SQL database query
	function query_statement($sql_statement)
	{
		// local vars
		$data_recieved;
		$query_type;		// (string), type of query (i.e. request, statement, other, etc)
		$result;
		$function_name;
		
		// get function name
		$trace = debug_backtrace();
		$function_name = $trace[0]["function"];
		
		// set request type
		$query_type = "statement";
		
		// send query to database and get results
		try {
			$data_recieved = $this->single_query($sql_statement, $query_type);
		}
		catch(FailedQueryException $ex) {			
			// set error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' recieved the following message: ".$ex->get_errorMessage();
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $ex->my_trace, $ex->query_array, $ex->sql, $ex->result);
		}
		
		if($data_recieved) {
			// set result to data recieved
			$result = $data_recieved;
		}
		else {
			// set result to data recieved
			$result = $data_recieved;
			
			// set and display error message
			$error_message = $this->error_message_header." The function '<b>".$function_name."()</b>' had an error. Single Query return <b>failed</b>. Please check your SQL query.";
			
			// thow an exception for the query return error
			throw new FailedQueryException($error_message, $my_trace=NULL, $query_array=NULL, $sql_statement, $result);
		}
		
		// return result
		return $result;
	}
}
?>