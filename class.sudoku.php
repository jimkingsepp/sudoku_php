<?php

include "class.nonet.php";

class Sudoku {
	const default_span = 9;
	private $span;
	private $subspan;
	private $cells = array (); // assoc. array with cell name as key
	                           // nonets
	private $squares = array ();
	private $columns = array ();
	private $rows = array ();

	public function __construct($span = self::default_span) {
		$this->init_span ( $span );
		$this->init_nonets ();
		$this->init_cells ();
		//print_r($this->cells);
		
		// TESTING...
		// $this->test_suite ();
	}

	public function setCellValues($initial_set) {
		//$initial_set = json_decode ( $json );
		
		foreach ( $initial_set as $cell_key => $cell_value ) {
			//echo $cell_key . " " . $cell_value . "\n";
			$this->cells [$cell_key]->setLockedValue ( $cell_value );
		}
		
		// TESTING...
		// $this->testCellValues ();
	}
	
	/*
	 * Returns true if the puzzle is solved,
	 * returns false otherwise
	 */
	public function solve() {
		foreach($this->squares as $idx => $nonet) {
			$nonet->examineCells();
		}
		foreach($this->rows as $idx => $nonet) {
			$nonet->examineCells();
		}
		foreach($this->columns as $idx => $nonet) {
			$nonet->examineCells();
		}
		
		//	check if puzzle is solved
		//	i.e., if all the cells have a locked value
		foreach($this->cells as $cell) {
			if($cell->getLockedValue() === null ) {
				return false;
			}
		}
		return true;
	}
	
	public function getCells() {
		$return_array = array();
		foreach ( $this->cells as $cell ) {
			$return_array[$cell->getName()] = implode("",$cell->getValues());
		}
		
		return $return_array;
	}
	
	public function reportCellValues() {
		foreach($this->cells as $cell) {
			echo $cell->getName() . ": ";
			echo implode(", ", $cell->getValues()) . "\n";
		}
		echo "\n";
	}

	/*
	 * Initializing functions
	 */
	private function init_span($span) {
		// set up span
		if ($span === NULL) {
			$this->span = self::default_span;
		}
		else {
			$this->span = $span;
		}
		
		// set up subspan
		$this->subspan = $this->square_root ( $this->span );
		if ($this->subspan === NULL) {
			throw new Exception ( "Base number must be a perfect square." );
		}
	}

	private function init_nonets() {
		for($idx = 0; $idx < $this->span; $idx ++) {
			$this->squares [$idx] = new Nonet ( $this->span );
			$this->columns [$idx] = new Nonet ( $this->span );
			$this->rows [$idx] = new Nonet ( $this->span );
		}
	}

	private function init_cells() {
		// column / row tags
		$columns = array ();
		$rows = array ();
		$alpha_value = 65; // ASCII 'A'
		$num_value = 1;
		for($idx = 0; $idx < $this->span; $idx ++) {
			$columns [$idx] = chr ( $alpha_value ++ );
			$rows [$idx] = $num_value ++;
		}
		
		// default values for cell
		// could put this in the loop above to save cycles
		$values = array ();
		for($idx = 1; $idx < $this->span + 1; $idx ++) {
			$values [] = $idx;
		}
		
		$subbase = - 1;
		$square_index = 0;
		foreach ( $rows as $row_index => $row ) {
			if ($row_index !== 0 && ($row_index + 1) % $this->subspan === 0) {
				$subbase += $this->subspan;
			}
			foreach ( $columns as $column_index => $column ) {
				$temp_cell = new Cell ( $column . $row, $values );
				$this->cells [$column . $row] = $temp_cell;
				$this->columns [$column_index]->add_cell ( $temp_cell );
				$this->rows [$row - 1]->add_cell ( $temp_cell );
				$this->squares [$square_index]->add_cell ( $temp_cell );
				
				if ($column_index === ($this->span - 1)) {
					$square_index = $subbase;
				}
				if (($column_index + 1) % $this->subspan === 0) {
					$square_index ++;
				}
			}
		}
	}

	/*
	 * Take square root of $val.
	 * Test to be sure that $val is a perfect square
	 *
	 * Returns integer (square root) if $val is perfect square
	 * Returns NULL otherwise
	 */
	private function square_root($val) {
		$root = sqrt ( $val ); // $root is a float
		$parts = explode ( '.', ( string ) $root );
		if (isset ( $parts [1] )) {
			return NULL;
		}
		return ( integer ) $parts [0];
	}

	/*
	 * Testing functions
	 */
	private function test_suite() {
		echo "SQUARES\n";
		$this->test ( $this->squares );
		
		echo "\nCOLUMNS\n";
		$this->test ( $this->columns );
		
		echo "\nROWS\n";
		$this->test ( $this->rows );
		
		echo "\nFULL GRID\n";
		$delim = '';
		foreach ( $this->cells as $idx => $cell ) {
			echo $delim . $cell->getName ();
			$delim = ", ";
			if ($idx !== 0 && ($idx + 1) % ($this->span) === 0) {
				echo "\n";
				$delim = '';
			}
		}
	}

	private function test($nonets) {
		foreach ( $nonets as $idx => $nonet ) {
			echo "Nonet $idx:\n";
			echo $nonet->test () . "\n\n";
		}
	}

	public function listCellValues() {
		echo "\n\nListing cell values: \n";
		$delim = ' | ';
		$idx = 0;
		foreach ( $this->cells as $cell ) {
			echo $delim . $cell->getName () . ": ";
			if ($cell->getLockedValue () === NULL) {
				//echo "-";
				echo "[" . implode("", $cell->getValues()) . "]";
			}
			else {
				echo $cell->getLockedValue ();
			}
			$delim = ' | ';
			$idx ++;
			if ($idx === $this->span) {
				echo "$delim\n";
				$idx = 0;
			}
		}
	}
}
