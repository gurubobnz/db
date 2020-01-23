<?php

	namespace GuruBob;
	use PDO;

	class DB {

		public function __construct($dsn, $username, $password) {
			$this->db = new PDO($dsn, $username, $password);
			$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->db->exec('SET NAMES utf8');
		}

		public function getLastInsertId() {
			return $this->db->lastInsertId();
		}

		public function execute($sql, $parameters = []) {
			$st = $this->db->prepare($sql);
			$st->execute($parameters);
		}

		public function fetchAll($sql, $parameters = []) {
			$st = $this->db->prepare($sql);
			$st->execute($parameters);
			return (array)$st->fetchAll(PDO::FETCH_ASSOC);
		}

		public function fetch($sql, $parameters = []) {
			$st = $this->db->prepare($sql);
			$st->execute($parameters);
			return $st->fetch(PDO::FETCH_ASSOC);
		}

		public function insert($table, $fields) {
			$sql = 'INSERT INTO `'.$table.'` SET ';
			$sql .= implode(', ', array_map(function($f) { return '`'.$f.'` = ?'; }, array_keys($fields)));
			return $this->execute($sql, array_values($fields));
		}

		public function startTransaction() {
			if(!$this->db->beginTransaction()) {
				throw new Exception('Could not start transaction');
			}
		}

		public function commitTransaction() {
			if(!$this->db->commit()) {
				throw new Exception('Could not commit transaction');
			}
		}

		public function rollbackTransaction() {
			if(!$this->db->rollBack()) {
				throw new Exception('Could not rollback transaction');
			}
		}

		public function transaction($closure) {
			try {
				$this->startTransaction();
				$result = $closure();
				$this->commit();
				return $result;
			} catch(\Exception $e) {
				$this->rollbackTransaction();
				throw $e;
			}
		}
	}
