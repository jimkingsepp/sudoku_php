<?php

class Nonet {
	private $cells;

	// array of cells
	// private $locked_values;
	
	// array of values that are set
	// private $subspan;
	public function add_cell(&$cell) {
		$this->cells [] = $cell;
	}

	public function examineCells() {
		// generate list of valid values for this nonet
		$locked_values = array ();
		foreach ( $this->cells as $cell ) {
			$locked_value = $cell->getLockedValue ();
			if ($locked_value !== null) {
				$locked_values [] = $locked_value;
			}
		}
		sort($locked_values);
		
		// pass these valid values into the cell
		// to let the cell adjust its possible values
		for ( $idx = 0; $idx < sizeof($this->cells); $idx++ ) {
			$cell = &$this->cells[$idx];
			$locked_value = $cell->getLockedValue ();
			//	if there is no locked-in value, 
			//	compare the locked-in values of the nonet 
			//	with the possible values of the cell
			if ($locked_value === null) {
				$locked_value = $cell->adjustValues ( $locked_values );
				//	if the adjustment yielded a locked-in value,
				//	add it to the array of locked-in values for the nonet
				//	and recheck.
				if($locked_value !== null) {
					$locked_values[] = $locked_value;
					sort($locked_values);
					$idx = -1;	//	reset
				}
			}
		}
	}

	public function test() {
		$delim = '';
		$subspan = sqrt ( sizeof ( $this->cells ) );
		foreach ( $this->cells as $idx => $cell ) {
			echo $delim . $cell->getName ();
			$delim = ", ";
			if ($idx !== 0 && ($idx + 1) % $subspan === 0) {
				echo "\n";
				$delim = '';
			}
		}
	}
}
class Cell {
	private $name;
	// potential values 1 thru n (usually 9)
	// array of possible values;
	// sizeof() === 1 if there is a locked_value
	private $possible_values;
	// the valid value; single digit or null
	private $locked_value;

	public function __construct($name = '', $values = []) {
		$this->setName ( $name );
		$this->setValues ( $values );
		$this->setLockedValue ( null );
	}

	public function getLockedValue() {
		return $this->locked_value;
	}

	public function setLockedValue($locked_value) {
		$this->locked_value = $locked_value;
		if ($locked_value !== null) {
			$this->possible_values = array (
					$locked_value 
			);
		}
	}

	public function getName() {
		return $this->name;
	}

	public function getValues() {
		return $this->possible_values;
	}

	private function setValues($values) {
		$this->possible_values = $values;
	}

	private function setName($name) {
		$this->name = $name;
	}

	/*
	 * Returns the locked value if there is only one possible value
	 * Otherwise returns null
	 */
	public function adjustValues($values) {
		$this->possible_values = array_diff ( $this->possible_values, $values );
		if(sizeof($this->possible_values) === 1) {
			$this->possible_values = array_values($this->possible_values);
			$locked_value = $this->possible_values[0];
			$this->setLockedValue($locked_value);
			return $locked_value;
		}
		else {
			return null;
		}
	}
}
