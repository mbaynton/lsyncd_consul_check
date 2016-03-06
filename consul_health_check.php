#!/usr/bin/env php
<?php
/**
 * Consul interpretation of return codes:
 * 0: Healthy
 * 1: Warning
 * Others: Failure
 */
require __DIR__ . '/vendor/autoload.php';
use SensioLabs\Consul;

function report($code, $msg) {
  fwrite(STDERR, $msg . "\n");
  exit($code);
}

 /* Name of the lsyncd config to monitor expected as $argv[1] */
if (count($argv) != 2){
  report (1, 'Health check script was not called correctly.');
}

$name = $argv[1];

// Write current time to the test directory
$t = time();
file_put_contents("/opt/consul_checks/sync_tests/local/$name/testfile", $t);

// Find our peer from consul service listings
$service_factory = new Consul\ServiceFactory();
/**
 * @var $catalog Consul\Services\Catalog;
 */
$catalog = $service_factory->get('catalog');
$peers = $catalog->service("lsyncd: $name");
var_dump($peers);
exit();
$status = "/var/log/lsyncd/$name.status";

$status_stat = stat($status);
if (! $status_stat || ! is_readable($status)) {
  report (1, "Could not find a readable status file at $status");
}

$mtime = $status_stat[9];
$last_write = time() - $mtime;
if ($last_write > 60 * 3) {
  $mins = round($last_write / 60);
  report (2, "No writes to status file for $mins minutes.");
}

/*
 * There's a line in the file of the format "There are \d+ delays" which is
 * often indicative of something not syncing.
 */
$status_str = file_get_contents($status);
$matches = [];
if (preg_match("/There are (\d+) delays/", $status_str, $matches) !== 1) {
  report (1, 'Could not find the delay count in the status file.');
}

if ($matches[1] !== "0") {
  report (1, $matches[0]);
}

report (0, 'lsyncd up and synchronized.');
