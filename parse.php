#!/usr/bin/php
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ($argv[1] == "-h" || $argv[1] == "--help" || $argv[1] == "" || $argv[2] == "") {
  echo "TCX data extraction by Gregory Chanez - http://www.nakan.ch/\n";
  echo "Read a TCX file and convert it to CSV format (; delimited) output in stdout\n";
  echo "Usage: parse.php <TCX file> [OPTIONS]\n";
  echo "<TCX file>: Path to a valid TCX file\n";
  echo "OPTIONS can be:\n";
  echo "--hr              - Extract HR data\n";
  echo "--elevation       - Extract elevation data\n";
  echo "--power           - Extract power data\n";
  echo "--timestamp       - Extract timestamp of each point\n";
  echo "--replacebyzero   - Replace null or negative values by 0\n";
  echo "--removezero      - Remove 0 values (for HR only)\n";
  echo "--headers         - Put fields title in the first line\n";
  exit();
}
// Manage parameters
$extract_hr = $extract_elevation = $extract_timestamp = $replacebyzero = $removezero = $put_headers = 0;
for ($i=2; $i<sizeof($argv); $i++) {
  switch ($argv[$i]) {
    case "--hr": $extract_hr = 1; break;
    case "--elevation": $extract_elevation = 1; break;
    case "--power": $extract_power = 1; break;
    case "--timestamp": $extract_timestamp = 1; break;
    case "--replacebyzero": $replacebyzero = 1; break;
    case "--removezero": $removezero = 1; break;
    case "--headers": $put_headers = 1; break;
  }
}

// Tweaks
$delimiter = ";";

// If headers are set
if ($put_headers) {
  if ($extract_hr) { echo "HR" . $delimiter; }
  if ($extract_power) { echo "Power" . $delimiter; }
  if ($extract_elevation) { echo "Elevation" . $delimiter; }
  if ($extract_timestamp) { echo "Timestamp" . $delimiter; }
  echo "\n";
}

// Loading XML file (TCX format expected but not validated).
$loadfile = simplexml_load_file($argv[1]);

// Normally, this TCX file will contain only one activity... No checks about this
foreach ($loadfile->Activities->Activity as $act) {
  // Looping over all Laps
  foreach ($act->Lap as $lap) {
    // Looping over all Tracks (should be 1 per lap but we never know...)
    foreach ($lap->Track as $track) {
        // For each Trackpoint
        foreach ($track->Trackpoint as $point) {
          // If HR is expected in the output
          if ($extract_hr) {
            $hr = $point->HeartRateBpm->Value;
            // If value is empty set to 0
            if ($hr == "") { $hr = 0; }
              // Output
              echo $hr . $delimiter;
          }

          // If power is expected in the output
          if ($extract_power) {
            $pw = $point->Extensions->TPX->Watts;
            if ($replacebyzero && ($pw == "" || intval($pw) < 0)) { $pw = 0; }
            echo $pw . $delimiter;
          }

          // If altitude is expected in the output
          if ($extract_elevation) {
            $al = $point->AltitudeMeters;
	          echo $al . $delimiter;
          }

          // If timestamp is expected in the output
          if ($extract_timestamp) { echo $point->Time . $delimiter; }
          echo "\n";
        }
      }
    }
}
?>
