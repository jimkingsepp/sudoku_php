<?php
require "class.sudoku.php";

if(isset($_REQUEST['q']) === false) {
	//	No value in the request.");
	$contents = file('puzzle_values.txt');
	$set = array();
	foreach($contents as $line) {
		$record = explode("\t",$line);
		$set[$record[0]] = intval($record[1]);
	}
}
else {
	$request = json_decode($_REQUEST['q']);
	$set = array();
	foreach($request as $obj) {
		$set[$obj->id] = $obj->value;
	}
}

try {
	$game = new Sudoku ( );
}
catch ( Exception $e ) {
	echo $e->getMessage () . "\n";
}

$game->setCellValues ( $set );
//	put a count on this in the event we can't solve this within 10 tries
$count = 0;	
while ( $game->solve () === false ) {
	$count++;
	if($count < 10) {
		break;
	}
}
$solution = $game->getCells();
echo json_encode($solution);

/*
set up dummy values

$initial_set = array (
		'C1' => 9,
		'I1' => 1,
		'C2' => 1,
		'E2' => 4,
		'G2' => 9,
		'B3' => 5,
		'C3' => 6,
		'A4' => 4,
		'E4' => 1,
		'H4' => 6,
		'A5' => 9,
		'D5' => 7,
		'G5' => 3,
		'D6' => 4,
		'E6' => 5,
		'I6' => 7,
		'D7' => 9,
		'G7' => 6,
		'H7' => 8,
		'D8' => 2,
		'I8' => 4,
		'A9' => 7,
		'D9' => 8
);
*/
