<?php

	/**
	 * @author Amine KABAB
	 * @link http://www.github.com/kabab/Model
	 *
	 */
	require_once('database.php');
	abstract class Model extends Database{
		/**
		 * Table name 
		 * @var string
		 */
		protected $table = null;

		/**
		* Primary key field name 
		* @var string
		*/

		protected $primaryKey = 'id';
		/**
		 * $validate = array('fieldname' => array(
		 *						'rule'=>'alpha',
		 *						'max'=>7,  // Max charactere number
		 *						'min'=>3, // Min charactere number 
		 *						'rquired'=>false, // don't validate is the field empty
		 *						'msg' => 'The field must have at minimum 3 characteres'
		 *					),
		 *					'fieldname' => array(
		 *						'rule'=>'numeric',
		 *						'max'=>7, // Max number  
		 *						'min'=>3, // Min number 
		 *						'rquired'=>false;
		 *					));
		 */
		public $validate = null;

		/**
		 * When you set a value to this variable the get() function
		 * will return just the row with index $id if it's null
		 * get() function will return all rows
		 *
		 * @var int
		 */
		public $id = null;

		/**
		 * this variable will be changed
		 * when you call paginate function 
		 * it will contain the number of 
		 * pages it depend on the first @param 
		 * in paginate function
		 */
		public $page_num = 1;

		function __construct(){
			parent::__construct();
		}

		/**
		 * Return all rows data
		 * @see $id
		 * @return array
		 */
		function get(){
			$query = "SELECT * FROM ".$this->table;
			if($this->id != null)
				$query .= " where ".$this->primaryKey." = ?";
			$sth = $this->dbh->prepare($query);
			$sth->execute(array($this->id));
			$result = $sth->fetchAll();
			return $result;

		}

		/**
		 * Return the last $num rows
		 * @param int
		 * @return array
		 */
		function getLast($num){
			$query = "SELECT * FROM ".$this->table;
			$query .= " ORDER BY ".$this->primaryKey." DESC LIMIT 0, ?";
			$sth = $this->dbh->prepare($query);
			$sth->execute(array($num));
			$result = $sth->fetchAll();
			return $result;
		}

		/**
		 * Return the first $num rows
		 * @param int
		 * @return array
		 */
		function getFirst($num){
			$query = "SELECT * FROM ".$this->table;
			$query .= " ORDER BY ".$this->primaryKey." ASC LIMIT 0, ?";
			$sth = $this->dbh->prepare($query);
			$sth->execute(array($num));
			$result = $sth->fetchAll();
			return $result;
		}

		/**
		 * Return the row with condition in @param
		 * @param array condition where array key is the field and the array
		 * value is the condition value
		 * @param array field the names of fields that you want to get 
		 * @param int line the number of line in the result
		 * @param int offset result 
		 * @return array
		 */
		function getWhere($condition,$field=null,$line=null,$offset=0){
			$query = 'SELECT ';
			// @var array will contain execute parametres 
			$exec_param = array();
			// add fields name to the query
			// array will contain field0 , filed1 ... field$n
			$fields_names = array();
			// iniale fields counter 
			if($field!=null){
				foreach ($field as $value) {
					$query .= "$value , ";
				}
				$query = substr($query, 0, strlen($query)-2);
			}else
				$query .=' *';

			$query .= " FROM ".$this->table." WHERE ";
			// add conditions
			foreach ($condition as $key => $value) {
				$query .= $key.' = ? AND ';
				$exec_param[] = $value;
			}
			$query = substr($query, 0,strlen($query)-4);
			// check the id if is set or not
			if($this->id != null){
				$query .= ' AND '.$this->primaryKey.' = ? ';
				$exec_param[] = $this->id;
			}
			elseif($line!=null){
					$query .= " LIMIT $offset , $line";
			}
			$sth = $this->dbh->prepare($query);
			$sth->execute($exec_param);
			$result = $sth->fetchAll();
			return $result;
		}

		/**
		 * if $id is set update just the row with id $id
		 * else update all rows ""
		 * 
		 */
		function update($data,$condition = null){
			$query = "UPDATE ".$this->table." SET ";
			foreach ($data as $key => $value) {
				$query .= "$key = '$value' ,";
			}
			$query = substr($query, 0,strlen($query)-1);
			if($condition != null ){
				$query .= 'where ';
				foreach ($condition as $key => $value) {
					$query .= "$key = '$value' and ";
				}	
				$query = substr($query, 0,strlen($query)-4);
				if($this->id != null)
					$query .= " and ".$this->primaryKey." = '".$this->id."'";
			}elseif ($this->id != null) {
				$query .= " where ".$this->primaryKey." = '".$this->id."'";
			}
			$sth = $this->dbh->prepare($query);
			$sth->execute();
		}

		/**
		 * Add data to the table 
		 * @param array
		 * @return TRUE on success FALSE on fealure 
		 */ 
		function add($data){
			$query = "INSERT INTO ".$this->table."(";
			$struct = "";
			$values = " VALUES(";
			foreach ($data as $key => $value) {
				$struct .= "$key,";
				$values .= ":$key,"; 
			}
			$struct = substr($struct, 0, strlen($struct)-1).")";
			$values = substr($values, 0, strlen($values)-1).")";
			$query .= $struct.$values;
			$sth = $this->dbh->prepare($query);
			$sth->execute($data);
		}
		/**
		 * @param array condition @see getWhere() 
		 * @return int the number of result
		 */

		function getRowsNumber($condition=array('1' => '1')){
			$query = "select count(*) from ".$this->table." WHERE";
			$values = array();
			foreach ($condition as $key => $value) {
				$query .= " $key = ? AND ";
				$values[] = $value;
			}
			$query = substr($query, 0, strlen($query)-4);
			$stmt = $this->dbh->prepare($query);
			$stmt->execute($values);
			$re = $stmt->fetchAll();
			return $re[0][0];
		}
		/**
		 * delete a row if id is defined the method will delete 
		 * the row with the specific is else it will delete all rows
		 * you can add an array that conatin condition of the rows
		 * that you wish to remove
		 * @param array conditions 
		 * @return boolean true on success o false on failure
		 *
		 */
		function delete($condition=array('1' => '1')){
			$query = "delete from ".$this->table." WHERE";
			$values = array();
			foreach ($condition as $key => $value) {
				$query .= " $key = ? AND ";
				$values[] = $value;
			}
			$query = substr($query, 0, strlen($query)-4);
			if($this->id != null){
				$query .= ' AND '.$this->primaryKey.' = ? ';
				$values[] = $this->id;

			}
			$stmt = $this->dbh->prepare($query);
			return $stmt->execute($values);
		}

		/**
		 * paginate method will return an array 
		 * that contain row in specific page
		 * to split the pages you need to fill
		 * row parameter 
		 * @param int rows number
		 * @param int page number
		 * @param array conditions 
		 * @param array fields
		 * @return array
		*/

		function paginate($row,$page=0,$condition=array('1' => '1'),$field=null){
			$offset = $page*$row;
			$this->page_num = ceil($this->getRowsNumber()/ $row);
			return $this->getWhere($condition,$field,$row,$offset);
		}

		/**
		 * 
		 * @param array that contian data that you wish to add
		 * @return 	array an empty array if data is valide else 
		 *			will return an assoc array that contain the 
		 *			name of the field and his error msg 
		 *			(fieldname => errorMsg)
		 *
		 */

		function valide($data){
			$error_msg = array();
			foreach ($this->validate as $key => $value) {
				$str = $data[$key];
				$error = false;
				if(empty($str) && !$value['required'])
					break;
				foreach ($value as $typecond => $cond) {
					if(!$this->_valide($str,$typecond,$cond)){
						echo $typecond;
						$error = true;
						break;
					}
				}
				if($error)
					$error_msg[$key] = $value['msg'];
			}
			return $error_msg;
		}
		/**
		 *
		 *
		 *
		 */
		private function _valide($str,$typecond, $cond){
			switch ($typecond) {
				case 'rule':
					return $this->pattern($str,$cond);
				case 'max':
					return strlen($str) <= $cond;
				case 'min':
					return strlen($str) >= $cond;
				case 'required':
					return !empty($str)+!$cond;
				case 'msg':
					return true;
				default:
					return false;
					break;
			}
		}
		/**
		 * @param string $str 
		 * @param string $rule (email | numeric | date | alphanumeric | alpha | name )
		 */
		private function pattern($str,$rule){
			switch ($rule) {
				case 'email':
					$pattern = "/^[[:alnum:]\_\-]+@[[:alnum:]\_\-]+\.[[:alnum:]]{1,3}$/";
					break;
				case 'numeric':
					$pattern = "/^\d+$/";
					break;
				case 'date':
					$pattern = "/^\d{2,4}(\-|\/)(1[0-2]|[1-9])(\-|\/)([0-2]\d|3[01]|[1-9])$/";
					break;
				case 'alphanumeric':
					$pattern = "/^[[:alnum:]]+$/";
					break;
				case 'alpha': 
					$pattern = "/^[[:alpha:]]+$/"; 
					break;
				case 'name':
					$pattern = "/^[[:alpha:]\s]+$/";
					break;
				default:
					return 0;
			}

			if(preg_match($pattern, $str))
				return 1;
			else 
				return 0;
		}

		
	}