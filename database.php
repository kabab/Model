<?php
	class Database{
		/**
		 * Database connection handler
		 * @var resource id
		 */
		protected $dbh = null;
		/**
		 * Database name
		 * @var string
		 */
		private $db = 'database';
		/**
		 * Database username
		 * @var string
		 */
		private $user = 'root';

		/**
		 * Database password
		 * @var string
		 */

		private $pass = '';
		
		/**
		 * Databas host
		 * @var string
		 */
		private $host = 'localhost';
		/**
		 * Create a object attached to the database
		 * 
		 */
		function __construct(){
			
			$url = 'mysql:host='.$this->host.';dbname='.$this->db;
			try{
				$this->dbh = new PDO($url, $this->user, $this->pass);
			}catch(PDOException $e){
				throw $e;
			}
		}
	}