#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ($argv[1] == "-h" || $argv[1] == "--help" || $argv[1] == "" || $argv[2] == "") {
  echo "Align two CSV output of parse.php by Gregory Chanez - http://www.nakan.ch/\n";
  echo "Usage: align.php <INPUT FILE 1> <INPUT FILE 2> <align> <timestamp col> <empty lines>\n";
  echo "<INPUT FILE N>: Path to file (CSV ; delimited)\n";
  echo "<align>: 1 or 0 (default 0) Output only points with the same timestamp on the same line\n";
  echo "<timestamp col>: if <align> is set to 1, set the column of timestamp value (should be the same for file 1 and 2)\n";
  echo "<empty lines>: 1 or 0 (default 0) If set, print lines of <INPUT FILE 1> even if no corresponding lines in <INPUT FILE 2>\n";
  exit();
}
// Open files in read mode (no checks)
$f1 = fopen($argv[1], "r");
$f2 = fopen($argv[2], "r");

// Put the content of file 1 in an array
while (($line = fgets($f1)) !== false) {
  $f1l[] = $line;
}
// Put the content of file 2 in an array
while (($line = fgets($f2)) !== false) {
  $f2l[] = $line;
}

// Find what is the longest file to put it as reference
if (sizeof($f1l) >= sizeof($f2l)) { $size = sizeof($f1l); } else { $size = sizeof($f2l); }

// Numbers of cols for file 1
$f1c = sizeof(explode(";", $f1l[0]));
// Numbers of cols for file 2
$f2c = sizeof(explode(";", $f2l[0]));

$titleLine = "";
for ($i=0; $i<$f1c; $i++) {
  $titleLine .= $argv[1] . ";";
}
for ($i=0; $i<$f2c; $i++) {
  $titleLine .= $argv[2] . ";";
}
$titleLine = substr($titleLine, 0, strlen($titleLine) -1);
echo $titleLine . "\n";

// If align is set to 1
if ($argv[3]) {
  for ($i = 0; $i < sizeof($f1l); $i++) {
    $ts1 = explode(";", $f1l[$i]);
    $ts1 = str_replace(' ', '', $ts1[$argv[4]]);
    $ts1 = preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2})\.([0-9]{3}Z)/', $ts1, $ts1_matches);
    $ts1t = $ts1_matches[1];
    $isavalue = 0;
    for ($j = 0; $j < sizeof($f2l); $j++) {
      $ts2 = explode(";", $f2l[$j]);
      $ts2 = str_replace(' ', '', $ts2[$argv[4]]);
      $ts2 = preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2})\.([0-9]{3}Z)/', $ts2, $ts2_matches);
      $ts2t = $ts2_matches[1];
      if ($ts1t == $ts2t) {
        echo str_replace(array("\n", "\r", " "), "", $f1l[$i]) . ";" . str_replace(array("\n", "\r", " "), "", $f2l[$j]) . "\n";
        $isavalue = 1;
      }
    }
    if (!$isavalue && $argv[5]) {
      echo str_replace(array("\n", "\r", " "), "", $f1l[$i]) . "\n";
    }
  }
}
else {
  // For each line of the longest file
  for ($i = 0; $i < $size; $i++) {
    // If line on file 1 is empty, complete separators
    if ($f1l[$i] == "") { for ($j = 0; $j < $f1c-1; $j++) { $f1l[$i] .= ";"; } }
    // If line on file 2 is empty, complete separators
    if ($f2l[$i] == "") { for ($j = 0; $j < $f2c-1; $j++) { $f2l[$i] .= ";"; } }
    // Put the lines together with the correct newlines
    echo str_replace(array("\n", "\r", " "), "", $f1l[$i]) . ";" . str_replace(array("\n", "\r", " "), "", $f2l[$i]) . "\n";
  }
}
