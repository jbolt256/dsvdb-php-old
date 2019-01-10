<?php
namespace dsvdb;
require_once __DIR__ . '/../autoload.php';
require_once 'Operator.php';

class Request {
	/*
	 * A typical request will look have the following fields:
	 * operator 		-- operator ID
	 * operatorPassword -- operator password, currently in plaintext
	 * action			-- what to do
	 * query			-- serialized data, typically an array of stuff.
	 */
	public function __construct(array $POST) {		
		/* Parameters required by each req_* function. */
		$this->params 	= [
			'authenticate' => []
			];
			
		/*
		 * Set up class variables.
		 */
		$this->POST = $POST;
		$this->action 	= $POST['action'];
		$this->queryRaw = empty($POST['query']) ? null : $POST['query'];
		$this->query 	= is_null($this->queryRaw)? null : unserialize($this->queryRaw);
	}
	
	/*
	 * @function handle
	 * Handle a new external request.
	 */
	public function handle() {		
		/* Check is_null and !empty first to prevent a parser E_USER_NOTICE */
		if ( !is_null($this->query) && !empty($this->query['table']) && strpos($this->query['table'], '/') !== false ) {
			$tableExplode = explode('/', $this->query['table']);
			$this->query['subcat'] = $tableExplode[0];	// Subcategory is first
			$this->query['table']  = $tableExplode[1];
		}
		
		/*
		 * Allow request to pass ONLY if it passes the parameter test, or if no parameters
		 * are provided.
		 */
		if ( is_null($this->query) || $this->checkParams() ) { 
			$function = "req_" . trim($this->action);
			return $this->$function();
		}
	}
	
	/*
	 * @function checkParams
	 * Ensure that there are enough parameters provided to execute action.
	 */
	private function checkParams() {
		$num = count($this->params[$this->action]);
		$track = 0;
		/* DEVWARN: Check to ensure at least 1 param has been provided */
		for ( $i = 0; $i < $num; $i++ ) {
			if ( isset( $this->query[$this->params[$i]] ) ) {
				$track++;
			}
		}
		return $track === $num;
	}
	
	/*** REQ FUNCTIONS ***/
	private function req_authenticate() {
		print "OK";
	}	
}

class RequestMulti {
	function __construct($POST) {
		$this->POST = $POST;
		$this->responses = [];
		
		/*
		 * These two parameters are required. 
		 */
		if ( !empty($POST['operator']) && !empty($POST['operatorPassword']) ) {
			$this->operator = $POST['operator'];
			$this->operatorPassword = $POST['operatorPassword'];
		} else {
			trigger_error(DSVDB_MESSAGES['request_tooFewParams'], E_USER_NOTICE);
			exit(0);
		}
			
		/* Create new operator instance */
		$this->Operator = new Operator($this->operator, $this->operatorPassword);
		
		/* Finally, ensure that authentication was successful. */
		if ( !$this->Operator->authenticate() ) {
			trigger_error(DSVDB_MESSAGES['request_invalidAuth'], E_USER_ERROR);
			exit(0);
		}
	}
	
	/* 
	 * @function cycle
	 * Cycles through requests (INCOMPLETE).
	 */
	public function cycle() {
		$n = intval($this->POST['n']);
		
		for ( $i = 1; $i <= $n; $i++ ) {
			$arrayData = [
				'action' => $this->POST["r{$i}_action"],
				'query'	 => $this->POST["r{$i}_query"]
				];
			$Req = new Request($arrayData);
			$Req->handle();
		}
	}
}
?>