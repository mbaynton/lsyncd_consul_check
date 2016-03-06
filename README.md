A pretty custom script for testing lsyncd health in my hosting environment with Hashicorp's consul.

It actively pushes new data through lsyncd and verifies them on the synchronized peer, through use
of many environmental dependencies.

Some environment-specific assumptions this script makes:
 - It will receive a name as the first command-line argument, call it $name. There will be corresponding
   log/status files in /var/log/lsyncd/$name.status and /var/log/lsyncd/$name.log
 - The paths /opt/consul_checks/sync_tests/local/$name and sync_tests/remote/$name are writable
   directories.
 - Consul exposes the results of this script's health checking as a service named "lsyncd: $name"
 - The peer that participates in this synchronization exposes the filesystem at 
   /opt/consul_checks/sync_tests/remote over http at a name-based host "lsyncd_health.local"

