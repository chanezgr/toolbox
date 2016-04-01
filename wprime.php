#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// ARG1: CSV file
// ARG2: CP
// ARG3: WPRIME
// ARG4: Fixed TAU

$TAUlive = array();
$CP = $argv[2];
$WPRIME = $argv[3];
$FTAU = $argv[4];

$elapsedSec = 0;
$totalBelowCP = 0;
$countBelowCP = 0;
$elapsedSec = 0;
$ITAUlive = 0;
$IFTAU = 0;
$tot = 0;
$W = $WPRIME;
// Read source data file
$f1 = fopen($argv[1], "r");
// Put the content of file 1 in an array
while (($line = fgets($f1)) !== false) {
  $line = explode(";", $line);
  $line = str_replace(array("\n", "\r", " "), "", $line[0]);
  $raw[] = $line;

  $tot += $line;
  $avg[] = $tot/($elapsedSec+1);


  if ($line < $CP) {
    $totalBelowCP += $line;
    $countBelowCP++;
  }
  if ($countBelowCP > 0) {
    $TAUlive[$elapsedSec] = 546.00 * exp(-0.01*($CP - ($totalBelowCP/$countBelowCP))) + 316;
  }
  else {
    $TAUlive[$elapsedSec] = 546 * exp(-0.01*($CP)) + 316;
  }

  if ($elapsedSec == 3600) {
    $TAUhr = $TAU[$elapsedSec];
  }

  $value = $line;
  if ($value < 0) {
    $value = 0;
  }

  if ($value > $CP) {
    $powerValue = $value - $CP;
  }
  else {
    $powerValue = 0;
  }

  // Wprime bal with live TAU
  $ITAUlive += exp($elapsedSec / $TAUlive[$elapsedSec]) * $powerValue;
  $output = exp(-$elapsedSec / $TAUlive[$elapsedSec]) * $ITAUlive;
  $value = $WPRIME - $output;
  $wprimebalTAUlive[] = $value;

  // Wprime bal with fixed TAU
  $IFTAU += exp($elapsedSec / $FTAU) * $powerValue;
  $output = exp(-$elapsedSec / $FTAU) * $IFTAU;
  $value = $WPRIME - $output;
  $wprimebalFTAU[] = $value;

  // Wprime differential
  if ($line < $CP) {
    $W  = $W + ($CP-$line)*($WPRIME-$W)/$WPRIME;
  }
  else {
    $W  = $W + ($CP-$line);
  }
  $differential[] = $W;


  $elapsedSec++;
}

$totalBelowCP = 0;
$countBelowCP = 0;
for ($i=0; $i < sizeof($raw); $i++) {
  if ($raw[$i] < 0) { $raw[$i] = 0; }
  if ($raw[$i] < $CP) {
    $totalBelowCP += $raw[$i];
    $countBelowCP++;
  }
}
if ($countBelowCP > 0) {
  $TAU = 546.00 * exp(-0.01*($CP - ($totalBelowCP/$countBelowCP))) + 316;
}
else {
  $TAU = 546 * exp(-0.01*($CP)) + 316;
}

$I = 0;
for ($i=0; $i<sizeof($raw); $i++) {

  $value = $raw[$i];
  if ($value < 0) {
    $value = 0;
  }
  if ($value > $CP) {
    $powerValue = $value - $CP;
  }
  else {
    $powerValue = 0;
  }
  $I += exp($i / $TAU) * $powerValue;
  $output = exp(-$i / $TAU) * $I;
  $value = $WPRIME - $output;
  $wprimereal[] = $value;
}
echo "Sec;Power;W'bal live TAU;W'bal fixed TAU; W'bal real;W'Bal Differential;PW Avg\n";

for ($i = 0; $i < sizeof($wprimereal); $i++) {
  echo $i . ";" . $raw[$i] . ";" . $wprimebalTAUlive[$i] . ";" . $wprimebalFTAU[$i] . ";" . $wprimereal[$i] . ";" . $differential[$i] . ";" . $avg[$i] . "\n";
}

?>
