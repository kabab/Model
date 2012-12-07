<?php

	/**
	 * @author Amine KABAB
	 *
	 * get(), getLast(), getById(int $id), getWhere(array $condition);
	 * updateById();
	 * add(array(data));
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
		 * When you set a value to this variable the get() function
		 * will return just the row with index $id if it's null
		 * get() function will return all rows
		 *
		 * @var int
		 */
		public $id = null;

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
				$query .= " where ".$this->primaryKey." = ".$this->id;
			$sth = $this->dbh->prepare($query);
			$sth->execute();
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
			$query .= " ORDER BY ".$this->primaryKey." DESC LIMIT 0,".$num;
			$sth = $this->dbh->prepare($query);
			$sth->execute();
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
			$query .= " ORDER BY ".$this->primaryKey." ASC LIMIT 0,".$num;
			$sth = $this->dbh->prepare($query);
			$sth->execute();
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
			// add fields name to the query
			if($field!=null){
				foreach ($field as $value) {
					$query .= $value.', ';
				}
				$query = substr($query, 0, strlen($query)-2);
			}else
				$query .=' *';
			$query .= " FROM ".$this->table." WHERE ";
			// add conditions
			foreach ($condition as $key => $value) {
				$query .= "$key = '$value' AND ";
			}
			$query = substr($query, 0,strlen($query)-4);
			// check the id if is set or not
			if($this->id != null)
				$query .= ' AND '.$this->primaryKey.' = '.$this->id;
			elseif($line!=null)
					$query .= ' LIMIT '.$offset.', '.$line;
			$sth = $this->dbh->prepare($query);
			$sth->execute();
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
				$values .= "'$value',"; 
			}
			$struct = substr($struct, 0, strlen($struct)-1).")";
			$values = substr($values, 0, strlen($values)-1).")";
			$query .= $struct.$values;
			$sth = $this->dbh->prepare($query);
			$sth->execute();
		}
		/**
		 * @param array condition @see getWhere() 
		 * @return int the number of result
		 */

		function getRowsNumber($condition=array('1' => '1')){
			$re = $this->getWhere($condition, array('count(*)'));
			return $re[0][0];
		}

		/**
		* @param int rows number
		* @param int page number
		* @return array
		*/

		function paginate($row,$page=0){
			$re = $this->getWhere();
		}
	}