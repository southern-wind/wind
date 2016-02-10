#!/bin/sh


# rsync to servers
pushDnsRestartBind() {
#index start
index=0;

# Count how many to loop on.
element_count=${#servers[@]};
((element_count++))
TIMEOUT=10;
RSYNC_TIMEOUT="-e \\\"ssh -o ConnectTimeout=$TIMEOUT\\\"";

LOG=$LOCATION/log/anycast_update.log

cd $LOCATION;

while [ "$index" -lt "$element_count" ]
do    # List all the elements in the array.
  if [[ ${servers[$index]} =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
  echo "Rsync starting ${servers[$index]} $(date)" >>  $LOG
  #echo "rsync $RSYNC_TIMEOUT -a -O --chown=${chown[$index]} zones/* ${user[$index]}@${servers[$index]}:${dir[$index]};" >> $LOG
  echo rsync -e \"ssh -o ConnectTimeout=$TIMEOUT\" -ptgoO --chown=${chown[$index]} zones/* ${user[$index]}@${servers[$index]}:${dir[$index]} >> $LOG 2>> $LOG
  #set -x
  rsync -e "ssh -o ConnectTimeout=$TIMEOUT" -ptgoO --chown=${chown[$index]} zones/* ${user[$index]}@${servers[$index]}:${dir[$index]} >> $LOG 2>> $LOG
  RESULT=$?;
  #echo "Result is $RESULT";

  if [ $RESULT -eq 0 ]; then
	# RELOAD BIND
    echo "ssh -o ConnectTimeout=$TIMEOUT ${user[$index]}@${servers[$index]} rndc reload" >> $LOG
    ssh -o ConnectTimeout=$TIMEOUT ${user[$index]}@${servers[$index]} rndc reload >> $LOG
  else
	#ERROR - Tell someone.
    echo "Failed rsync to ${servers[$index]}" >> $LOG
    echo "Failed to rsync to ${servers[$index]} ERROR = $RESULT

       1      Syntax or usage error
       2      Protocol incompatibility
       3      Errors selecting input/output files, dirs
       4      Requested  action not supported: an attempt was made to manipulate 64-bit files on a platform 
              that cannot support them; or an option was specified that is supported by the client and not by the server.
       5      Error starting client-server protocol
       6      Daemon unable to append to log-file
       10     Error in socket I/O
       11     Error in file I/O
       12     Error in rsync protocol data stream
       13     Errors with program diagnostics
       14     Error in IPC code
       20     Received SIGUSR1 or SIGINT
       21     Some error returned by waitpid()
       22     Error allocating core memory buffers
       23     Partial transfer due to error - Folder permissions perhaps?
       24     Partial transfer due to vanished source files
       25     The --max-delete limit stopped deletions
       30     Timeout in data send/receive
       35     Timeout waiting for daemon connection
	" | mail $EMAIL -s 'wind dns update failure'
  fi 
  fi #End of valid ip.
  ((index++))
done
}


# Create New Zone file
# - REQUIRES: named-checkzone (ubuntu - apt-get install bind9utils)

createNewZone() {

echo "$(date) Starting zone update for $CUR_ZONE ..." >> $LOG

# Check that an existing file was passed as an argument by the caller.
if [ -z $CUR_ZONE ]; then
   echo "Usage: $0 ZONE_FILENAME"
   exit
fi

if [ ! -e $ZONES_ROOT$CUR_ZONE ]; then
   echo "file '$ZONES_ROOT$CUR_ZONE' does not exist" 1>&2
   exit
fi

# Check the syntax of the current zone file and make sure it includes "; serial" line
#echo $CUR_ZONE $ZONES_ROOT$CUR_ZONE;
if ! ( named-checkzone -q $CUR_ZONE $ZONES_ROOT$CUR_ZONE && grep -q "; serial" $ZONES_ROOT$CUR_ZONE ) ; then
   echo "`date` - $ZONES_ROOT$CUR_ZONE has errors (wrong syntax or  missing '; serial' comment)." 1>&2
   echo "#### START of named-checkzone output (if OK then '; serial' comment is missing) ####" 1>&2
   echo "`named-checkzone $CUR_ZONE $ZONES_ROOT$CUR_ZONE`" 1>&2
   echo "#### END of named-checkzone output ####" 1>&2
   exit
fi
 
# Where we will temporarily save the php-generated zone file.
PHP_ZONE="/tmp/php-zone"

# Where we will temporarily save the stripped (without the "serial" line) current zone file.
CUR_ZONE_S="/tmp/cur-zone-s"

# Where we will temporarily save the stripped (without the "serial" line) php-generated zone file.
PHP_ZONE_S="/tmp/php-zone-s"

# Get all new Zone file info from WiND DB
cd $LOCATION
php $PHP_SCRIPT $CUR_ZONE > $PHP_ZONE

# Extract the serial number of the current zone file.
# Remember to change '2' to '3' on the next millenium change. :P
CUR_SERIAL=`grep "; serial" $ZONES_ROOT$CUR_ZONE | grep -o "2........."`

# Check the validity of CUR_SERIAL
CUR_SERIAL_CHARS=`echo -n $CUR_SERIAL | wc -m`
if [ $CUR_SERIAL_CHARS -ne 10 ] || ! date -d `echo $CUR_SERIAL | cut -c 1-8` > /dev/null 2>&1 ; then
    echo "`date` - Serial line in $ZONES_ROOT$CUR_ZONE is not valid." 1>&2
    exit
fi

# Day Of CUR_SERIAL
DCS=`echo $CUR_SERIAL | tail -c +7 | head -c 2`

# Version of CUR_SERIAL
VCS=`echo $CUR_SERIAL | tail -c +9`

# If it is less than or equal to 9 delete leading zero (so that 08 or less is not interpreted as octal)
if [ $VCS -le 9 ]; then
  if [ $VCS -ne 0 ]; then
     VCS=`echo $VCS|tr -d 0`
  else
     VCS=0
  fi
fi

# Remove "serial" lines.
grep -v "; serial" $ZONES_ROOT$CUR_ZONE > $CUR_ZONE_S
grep -v "; serial" $PHP_ZONE > $PHP_ZONE_S

# If stripped versions of current zone file and php-generated zone file are identical, remove temp files and exit.
# Else, replace current zone file with the php-generated zone file and include the proper serial line.
if diff $CUR_ZONE_S $PHP_ZONE_S > /dev/null ; then
  if [ $DEBUG == "true" ]; then
	echo "debug: no changes, exiting.";
  fi
  echo "No changes $CUR_ZONE" >> $LOG
  rm -f $PHP_ZONE $CUR_ZONE_S $PHP_ZONE_S
  exit
else
  ## DNS Zone was different. Lets ammend and upload

  # Verion of Serial to Append.
  VSA="00"
  
  # Day of the Month, Now.
  DMN=`date +"%d"`
  
  # If day has not changed append previous serial version incremented by 1.
  if [ "$DMN" -eq "$DCS" ]; then
     let VSA=VCS+1
     
     # if VSA is less than or equal to 9, prepend a '0' to make it a 2 digit number.
     if [ "$VSA" -le 9 ]; then
        VSA="0$VSA"
     fi
  fi
  
  # The new serial.
  NEW_SERIAL=`date +"%Y%m%d"`$VSA
  
  # Serial Line Number.
  SLN=`grep -n "; serial" $PHP_ZONE | head  -c 1`
  # Line Number Before Serial.
  let LNBS=SLN-1
  # Line Number After Serial.
  let LNAS=SLN+1
  
  # Build the final zone file
  head -n $LNBS $PHP_ZONE > /tmp/$CUR_ZONE
  echo "				$NEW_SERIAL ; serial" >> /tmp/$CUR_ZONE
  tail -n +$LNAS $PHP_ZONE >> /tmp/$CUR_ZONE

  # Check that the final zone file passes bind's builtin function 'named-checkzone'.
   UNIXTIMESTAMP=`date +%s`
  if named-checkzone -q $CUR_ZONE /tmp/$CUR_ZONE ; then
     mv -f /tmp/$CUR_ZONE $ZONES_ROOT/archive/$CUR_ZONE-$UNIXTIMESTAMP
     cp  $ZONES_ROOT/archive/$CUR_ZONE-$UNIXTIMESTAMP  $ZONES_ROOT/$CUR_ZONE
	##
	## Now move to each server.
	##
	echo "new zone info, starting zone push" >> $LOG	
	#Function to do the work
	$(pushDnsRestartBind);
  else
     echo "`date` - PHP-generated file has errors (possible database failure)" 1>&2
     echo "#### START of named-checkzone output ####" 1>&2
     echo "`named-checkzone -d $CUR_ZONE /tmp/$CUR_ZONE`" 1>&2
     echo "#### END of named-checkzone output ####" 1>&2
	# Backup file before its lost.
     cp -f /tmp/$CUR_ZONE $ZONES_ROOT/broken/$CUR_ZONE-broken-$UNIXTIMESTAMP
     if [ $DEBUG == "true" ]; then
        echo "debug: ERROR zones files has errors - see file";
	echo "debug: $ZONES_ROOT/broken/$CUR_ZONE-broken-$UNIXTIMESTAMP";
     fi 
     rm -f $PHP_ZONE $CUR_ZONE_S $PHP_ZONE_S /tmp/$CUR_ZONE
     echo "Broken ZONE file made. please investigate.
	/tmp/$CUR_ZONE $ZONES_ROOT/broken/$CUR_ZONE-broken-$UNIXTIMESTAMP
	" | mail $EMAIL -s 'wind dns zone generation failure'
     exit
     fi

fi
}
