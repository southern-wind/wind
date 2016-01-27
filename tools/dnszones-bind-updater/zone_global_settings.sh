#!/bin/bash

####
# Zones that we want to check.
####

# TLD for forward.example
ZONES[0]=forward.example

# Reverse for 10.60.0.0/16
ZONES[1]=60.10.in-addr.arpa

####
# Whom to email errors
####
EMAIL=email@example.org

# Our list of Anycast Servers 
#
# Where new DNS zone info will get pushed out to.
# NOTES:
# - Make sure that the var numbering is sequential and no numbers are missed.
# - MAke sure the Server is by its IP!
# - user = who to login as. MUST HAVE:
#	- access to upload dir
#	- rndc reload (restart bind)
#	- SSH key for automated login.
# - dir = Dir to upload the zone file.
# - chown = whom to chown the zone file as once uploaded

#anycast server 1
servers[0]="10.60.22.22";
user[0]="root";
dir[0]="/etc/bind/anycast/";
chown[0]="root:root";

#anycast server 2 
servers[1]="10.60.33.33";
user[1]="wind";
dir[1]="/etc/bind/anycast/";
chown[1]="wind:windBind";





# Path to this location - DONT TOUCH!
LOCATION=$( dirname "${BASH_SOURCE[0]}" )

# Load functions - DONT TOUCH!
. $LOCATION/zone_functions.sh;