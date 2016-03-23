#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ($argv[1] == "-h" || $argv[1] == "--help" || $argv[1] == "" || $argv[2] == "") {
  echo "Data smoothing by Gregory Chanez - http://www.nakan.ch/\n";
  echo "Read a CSV file and smooth one column by a given number of seconds. Output CSV in stdout\n";
  echo "Usage: smooth.php <CSV File> <COL> <SEC> <IGNORE FIRST LINE>\n";
  echo "<CSV file>: Path to a valid CSV file\n";
  echo "<COL>: Column number to smooth in CSV file (first is 0)\n";
  echo "<SEC>: Number of seconds (or number of line) for smoothing\n";
  echo "<HEADER>: If set to 1, first line will be ignored and put as is in output [0/1] (default: 0)\n";
  exit();
}
// Variables and tweaks
$delimiter = ";";
$ignore = 0;
$x = $argv[3];
if ($argv[4] == "1") { $ignore = 1; }
$col = $argv[2];

// Read file
$f1 = fopen($argv[1], "r");
while (($line = fgets($f1)) !== false) {
  $f1l[] = $line;
}

// Numbers of cols
$f1c = sizeof(explode($delimiter, $f1l[0]));

// Manage the header parameter
if ($ignore) {
  $start = 1;
  echo str_replace(array("\n", "\r", " "), "", $f1l[0]) . "\n";
}
else { $start = 0; }

// Loop for every line of the input CSV file
for ($i = $start; $i < sizeof($f1l); $i++) {
  $line = explode($delimiter, $f1l[$i]);

  // Smooth the actual data
  for ($j = $x-1; $j>0; $j--) {
    $avg[$j] = $avg[$j-1];
  }
  $avg[0] = $line[$col];
  $smooth = 0;
  for ($j = 0; $j < $x; $j++) {
    $smooth += $avg[$j];
  }
  $smooth = $smooth/$x;

  // Rebuild the CSV with this smoothed data
  for ($j = 0; $j<$f1c; $j++) {
    if ($j == $col) { echo $smooth . $delimiter; }
    else { echo str_replace(array("\n", "\r", " "), "", $line[$j]) . $delimiter; }
  }
  echo "\n";
}
