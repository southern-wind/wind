<?php

// V20160127 - Initial Release as used by WAFN in conjunction with WiND. - JammiN
//
//
// To-Do:  Generate CNAMES out of CNAME Table.

if (isset($argv[1])) {
    $basedir = dirname($_SERVER['PHP_SELF']);
    //print_R($_SERVER);
    $conf_file = $basedir.'/'.basename($argv[1]).'.conf';
} else {
    $conf_file = basename($_GET['zonefile']).'.conf';
}

if ( file_exists($conf_file) ) {
    include $conf_file;
} else {
    echo "Conf File Missing\n";
    exit;
}
if (!isset($conf)) exit;

$mysql_link = mysql_connect($conf['db']['server'], $conf['db']['username'], $conf['db']['password']);

mysql_select_db($conf['db']['database'], $mysql_link);

function replace($array, $string) {
    $ret = $string;
    foreach ($array as $key => $value) {
	$ret = str_replace("##".$key."##", $value, $ret);
    }
    return $ret;
}

if ($conf['zone_type'] == 'dns-ip') {
    $debug=0;
    $replace = array('NAMESERVERS' => '', 'ZONES' => '', 'NS-SUBDOMAIN' => '', 'SERIAL' => time());
    //$s = explode('.',$conf['ns_auth']);
    $d = explode('.',$conf['ns_domain']);
    $dd = explode('.',$domain);
    if ($dd[0] != $d[1]) {
	$sub_domain = $dd[0];
	if ($debug) echo "Have subdomain $sub_domain\n";
	$sql = "Select * from dns_zones
	    where name ='$sub_domain'
	    limit 1";
	$q = mysql_query($sql, $mysql_link);
	if ($debug) { echo "SQL: $sql\n"; }
	while ($ret = mysql_fetch_assoc($q)) {
	    $sub_domain_zone_id = $ret['id'];
	}
	if (! isset( $sub_domain_zone_id) || ! is_numeric($sub_domain_zone_id)){
	    echo "Sub-domain doesn't exist\n";
	    exit;
	}
    }
    //print_R($s);
    //Check this node exists.
    // This is for the anycast DNS only
    $sql = "SELECT dns_nameservers.id AS ns_id, 
	dns_nameservers.name AS ns_num, 
	nodes.name_ns as ns_name,
	dns_nameservers.ip AS ns_ip
	    FROM dns_nameservers, nodes_script AS nodes
	    where dns_nameservers.node_id = nodes.id
	    and dns_nameservers.status =  'active'
	    and dns_nameservers.ip = '".ip2long($conf['ns_ip'])."'
	    order by ns_id";
    if ($debug) echo $sql."\n";
    $q = mysql_query($sql, $mysql_link);
    while ($ret = mysql_fetch_assoc($q)) {
	$hostname = $ret['ns_num'].".".$ret['ns_name'].".".$conf['ns_domain'].'.';
	$replace['NAMESERVERS'] .=  " NS $hostname\n";
	$anycastNameServerID = $ret['ns_id'];
	if ( isset( $checkIP[$ret['ns_ip']] ) ) {
	    //Crete a cname
	    if ($debug) { echo "IP $ret[ns_ip] exists. CNAMING $hostname\n"; }
	    $replace['ZONES'] .= $hostname
		." \t CNAME \t ".$checkIP[$ret['ns_ip']]."\n";
	}  else {
	    if ($debug) { echo "new IP $ret[ns_ip] for $hostname.\n"; }
	    $replace['ZONES'] .= $hostname
		." \t IN A \t ".long2ip($ret['ns_ip'])."\n";
	}
	if (!isset($checkIP[$ret['ns_ip']])) { $checkIP[$ret['ns_ip']] = $hostname; }
    }

    // All forward/reverse ZONES and which NS they point to (NOT anycast)
    // Do these first
    // This way the NS will always be an A record rather than a CNAME (illegal)
    $sql = "SELECT distinct(zone_id), nameserver_id, dns_zones.type AS type,
	dns_zones.name AS zone_name, dns_nameservers.name AS ns_name,
	nodes.name_ns AS ns_zone_name, dns_nameservers.ip AS ns_ip,
	dns_nameservers.status as ns_status
	    FROM dns_zones_nameservers,  `dns_zones` ,  dns_nameservers, nodes_script AS nodes
	    where  dns_nameservers.id !=  '$anycastNameServerID'
	    AND dns_zones_nameservers.zone_id = dns_zones.id
	    AND dns_zones_nameservers.nameserver_id = dns_nameservers.id
	    AND dns_nameservers.node_id = nodes.id 
	    order by zone_id";
    if ($debug) echo "SQL Zones NS: $sql \n";
    $q = mysql_query($sql, $mysql_link);

    while ($ret = mysql_fetch_assoc($q)) {
	if ($ret['type'] !== 'forward') { continue; }

	$subDom = $ret['zone_name'].".".$d[1];
	$zoneDomain = $domain;
	$endsWith = (substr( $subDom, -strlen( $zoneDomain ) ) == $zoneDomain);
	if ($debug) echo "endsWith=$endsWith - subDom=$subDom, zoneDomain=$zoneDomain\n";
	if ($endsWith) {
	    //If it endsWith, then it is a subdomain that we need to add.
	    if ($debug) {
		echo "endsWith, adding NS
		    ".$ret['ns_name'].'.'. $ret['ns_zone_name'].".$conf[ns_domain].\n";
	    }
	    if ($ret['ns_status'] == 'active') {  //only if NS is active
		$replace['NS-SUBDOMAIN'] .= $ret['zone_name'].".".$d[1].". \t IN NS \t ".
		    $ret['ns_name'].'.'. $ret['ns_zone_name'].".$conf[ns_domain].\n";
		if ($debug) {
		    echo "added: ". $ret['zone_name'].".".$d[1].". \t IN NS \t ".
			$ret['ns_name'].'.'. $ret['ns_zone_name'].".$conf[ns_domain].\n";
		}
	    }
	    if ($debug) {
		echo $ret['ns_name'].'.'. $ret['ns_zone_name'].".$conf[ns_domain]
		    checkNS:".(!isset($checkNS[$ret['ns_ip']]))."\n";
	    }
	    if (!isset($checkNS[$ret['ns_ip']]) ) {
		$sql = "SELECT dns_nameservers.id AS ns_id,
		    dns_nameservers.name AS ns_num,
		    nodes.name_ns as ns_name,
		    dns_nameservers.ip AS ns_ip
			FROM dns_nameservers, nodes_script AS nodes
			where dns_nameservers.node_id = nodes.id
			and dns_nameservers.status =  'active'
			and dns_nameservers.ip = '$ret[ns_ip]'
			order by ns_id";
		if ($debug) echo "SQL DNS-not anycast: $sql\n";
		$z = mysql_query($sql, $mysql_link);
		while ($r = mysql_fetch_assoc($z)) {
		    $hostname = $r['ns_num'].".".$r['ns_name'].".".$conf['ns_domain'].".";
		    //$replace['NAMESERVERS'] .=  " NS $hostname\n";
		    $nameServerID = $r['ns_id'];
		    if ($debug) echo "doing dns host $hostname (id:$nameServerID)\n";
		    if ( isset( $checkIP[$r['ns_ip']] )
			    && $checkIP[$r['ns_ip']] == $hostname) {
			//Already have record, do nothing
			if ($debug) echo "$hostname already has record .. skipping\n";
			continue;
		    } else if ( isset( $checkIP[$r['ns_ip']] ) ) {
			//Crete a cname
			$replace['ZONES'] .= $hostname
			    ." \t CNAME \t ".$checkIP[$r['ns_ip']]."\n";
		    }  else {
			$replace['ZONES'] .= $hostname
			    ." \t IN A \t ".long2ip($r['ns_ip'])."\n";
		    }
		    $checkNS[$ret['ns_ip']] = $hostname;
		    if (!isset($checkIP[$ret['ns_ip']])) { $checkIP[$r['ns_ip']] = $hostname; }
		}

	    }
	} elseif ($debug) {
	    echo "Something broke";
	}
    } //End While

    //Get data.
    // All forward/reverse records for Anycast
    $sql = "SELECT zone_id, nameserver_id, dns_zones.type AS type, 
	dns_zones.name AS zone_name, 
	dns_nameservers.name AS ns_name, dns_nameservers.ip, 
	hostname, ip_addresses.ip as hostname_ip
	    FROM dns_zones_nameservers,  `dns_zones` , ip_addresses, dns_nameservers 
	    WHERE dns_nameservers.id = '$anycastNameServerID'
	    AND dns_zones_nameservers.zone_id = dns_zones.id
	    AND dns_zones_nameservers.nameserver_id = dns_nameservers.id
	    AND ip_addresses.node_id = dns_zones.node_id 
	    AND ip_addresses.zone_type = 'forward' "
	    .(isset($sub_domain_zone_id)?" and zone_id = '$sub_domain_zone_id'":"")
	    ." ORDER BY  `dns_zones_nameservers`.`zone_id` ASC";
    if ($debug) echo "SQL - Anycast Records:$sql \n";
    $q = mysql_query($sql, $mysql_link);
    while ($ret = mysql_fetch_assoc($q)) {
	if ($ret['type'] !== 'forward') { continue; }
	//if ($debug) echo "endsWith=$endsWith - subDom=$subDom, zoneDomain=$zoneDomain\n";

	$endsWithDot = (substr($ret['hostname'], -1) == ".");
	$zoneDomain = $ret['zone_name'].".".$d[1] .($endsWithDot?'.':'');
	$checkZone[$ret['zone_id']][] = $zoneDomain;
	$endsWith = (substr( $ret['hostname'], -strlen( $zoneDomain ) ) == $zoneDomain );
	if (!$endsWith) { 
	    foreach ($checkZone[$ret['zone_id']] as $Z) {
		$endsWith = (substr( $ret['hostname'], -strlen( $Z ) ) == $Z);
		if ($endsWith) { break; }
	    }
	}
	if ($debug) echo "endsWith=$endsWith, endsWithDot=$endsWithDot - host=$ret[hostname], zoneDomain=$zoneDomain\n";
	$hostname = $ret['hostname'].($endsWith?'':".$zoneDomain");
	//Looks like some bad data.
	//Ignore duplicates
	if (isset($checkIP[$ret['hostname_ip']]) 
		&& $hostname == $checkIP[$ret['hostname_ip']]) {
	    continue;
	}
	if ( isset( $checkIP[$ret['hostname_ip']] ) ) { 
	    //Crete a cname
	    $replace['ZONES'] .= "$hostname".($endsWithDot?'':'.')
		." \t CNAME \t ".$checkIP[$ret['hostname_ip']]."\n";
	}  else {
	    if ($debug) { echo "new IP $ret[hostname_ip] for $hostname.\n"; }
	    $replace['ZONES'] .= "$hostname".($endsWithDot?'':'.')
		." \t IN A \t ".long2ip($ret['hostname_ip'])."\n";
	}
	//Save it for checking later
	if (!isset($checkIP[$ret['hostname_ip']])) { $checkIP[$ret['hostname_ip']] = $hostname; }
    }
}
if ($conf['zone_type'] == 'forward') {

    $replace = array('NAMESERVERS' => '', 'ZONES' => '', 'NS-SUBDOMAIN' => '', 'SERIAL' => '');

## NAMESERVERS
    $query = "SELECT dns_nameservers.name AS ns_num, dns_nameservers.ip AS ns_ip, nodes.name_ns AS name_ns
	FROM dns_nameservers
	INNER JOIN nodes ON nodes.id = dns_nameservers.node_id
	WHERE dns_nameservers.status = 'active'
	ORDER BY nodes.name_ns ASC, dns_nameservers.name ASC";
    $q = mysql_query($query, $mysql_link);
    //echo "SQL: $query";
    while ($ret = mysql_fetch_assoc($q)) {
	$replace['NAMESERVERS'] .= isset($conf['notify'])?
	    long2ip($ret['ns_ip']).";\n":
	    " NS ".$ret['ns_num'].".".$ret['name_ns'].$conf['ns_domain']."\n";
    }

## ZONES
    $query = "SELECT dns_zones.name AS zone_name, dns_nameservers.name AS ns_num, nodes.name_ns AS name_ns
	FROM dns_zones
	INNER JOIN dns_zones_nameservers ON dns_zones.id = dns_zones_nameservers.zone_id
	INNER JOIN dns_nameservers ON dns_zones_nameservers.nameserver_id = dns_nameservers.id
	INNER JOIN nodes ON dns_nameservers.node_id = nodes.id
	WHERE dns_nameservers.status = 'active' AND dns_zones.type = 'forward' AND dns_zones.status = 'active'
	ORDER BY dns_zones.name ASC, dns_zones_nameservers.id ASC";
    $q = mysql_query($query, $mysql_link);
    echo mysql_error();
    while ($ret = mysql_fetch_assoc($q)) {
	$replace['ZONES'] .= $ret['zone_name']." NS ".$ret['ns_num'].".".$ret['name_ns'].$conf['ns_domain']."\n";
    }

## NS-SUBDOMAIN
    $query = "SELECT dns_nameservers.ip AS ip, dns_nameservers.name AS ns_num, nodes.name_ns AS name_ns
	FROM dns_nameservers
	INNER JOIN nodes ON nodes.id = dns_nameservers.node_id
	WHERE dns_nameservers.status = 'active'
	ORDER BY nodes.name_ns ASC, dns_nameservers.name ASC";
    $q = mysql_query($query, $mysql_link);
    //echo "sql: $query";
    while ($ret = mysql_fetch_assoc($q)) {
	$replace['NS-SUBDOMAIN'] .= $ret['ns_num'].".".$ret['name_ns'].$conf['ns_domain']." A ".long2ip($ret['ip'])."\n";
	if ($ret['ns_num'] == 'ns0') {
	    $replace['NS-SUBDOMAIN'] .= $ret['name_ns'].$conf['ns_domain']." CNAME ".$ret['ns_num'].".".$ret['name_ns'].$conf['ns_domain']."\n";	
	}
    }


} elseif ($conf['zone_type'] == 'reverse') {
    $debug = 0;

    $replace = array('NAMESERVERS' => '', 'ZONES' => '', 'SERIAL' => time()) ;

## ZONES

    $query = "SELECT dns_zones.name AS zone_name, dns_nameservers.name AS ns_num, nodes.name_ns AS name_ns
	FROM dns_zones, nodes_script AS nodes, dns_zones_nameservers, dns_nameservers
	WHERE dns_zones.id = dns_zones_nameservers.zone_id
	AND dns_zones_nameservers.nameserver_id = dns_nameservers.id
	AND dns_nameservers.node_id = nodes.id
	AND dns_nameservers.status = 'active' AND dns_zones.type = 'reverse' AND dns_zones.status = 'active'
	ORDER BY dns_zones.name ASC, dns_zones_nameservers.id ASC";
    $q = mysql_query($query, $mysql_link);
    echo mysql_error();
    //echo "$query \n";
    //$hostname = $ret['ns_num'].".".$ret['ns_name'].".".$conf['ns_domain'].'.';
    $replace['NAMESERVERS'] .=  " NS $conf[master_dns].\n";
    while ($ret = mysql_fetch_assoc($q)) {
	$this_ns = $ret['ns_num'].".".$ret['name_ns'].".".$conf['ns_domain'];
	if ($conf['master_dns'] != $this_ns) {
	    /*
	    // Normal /24 way, but wafn uses delegation, which requires GENERATE.
	    .$ret['ns_num'].".".$ret['name_ns'].".".$conf['ns_domain'].".\n";
	     */
	    /* 
	    // It gets used like this.
	    0.30-24                 NS      ns.tic.wafn.
	    ;tic hill
	    $GENERATE 1-254 $.30    CNAME   $.30.0.30-24 ; unqualified
	     */
	    $ipsplit = explode('.',$ret['zone_name']);
	    $ip_id = $ipsplit[0];
	    $replace['NAMESERVERS'] .= "0.$ip_id-24  NS "
		.$ret['ns_num'].".".$ret['name_ns'].".".$conf['ns_domain'].".\n".
		"\$GENERATE 1-254 $.$ip_id CNAME $.$ip_id.0.$ip_id-24 ; unqualified\n\n";
	}
    }

    $sql = "SELECT ip_addresses . * , nodes . * 
	FROM  `ip_addresses` ,  `nodes_script` AS nodes
	WHERE ip_addresses.node_id = nodes.id
	AND zone_type =  'reverse'
	ORDER BY node_id ASC ";

    $q = mysql_query($sql, $mysql_link);
    while ($ret = mysql_fetch_assoc($q)) {
	$node_id = $ret['node_id'];
	//echo "node id $node_id\n";
	// Each NS with a reverse delegation.
	$zone_ip_exp = explode('.',$conf['zone_suffix']);
	$zone_ip=flip_ip($conf['zone_suffix']);
	//echo "zone_ip $zone_ip\n";

	//print_R($r); echo "\n";
	$full_ip = long2ip($ret['ip']);
	//If part of domain.
	$short_ip_exp = explode($zone_ip,$full_ip);
	$ip_ptr = flip_ip($short_ip_exp[1]);
	//echo "long $full_ip, short $short_ip_exp[1]\n";
	$d = explode('.',$conf['ns_domain']);
	$zoneDomain = $ret['name_ns'].".".$d[1];
	$endsWith = (substr( $ret['hostname'], -strlen( $zoneDomain ) ) == $zoneDomain);
	if ($debug) { 
	    echo "endsWith=$endsWith - host=$ret[hostname], zoneDomain=$zoneDomain\n";
	    print_R($ret); 
	}
	if (!$endsWith) {
	    //So hostname didn't end in sub.domain
	    //they may have pointed to another sub.domain lets check
	    $endsWith = (substr( $ret['hostname'], -strlen( $d[1] ) ) == $d[1]);
	}
	$hostname = $ret['hostname'].($endsWith?'':".$zoneDomain");


	$replace['ZONES'] .= "$ip_ptr IN PTR $hostname.\n"; 
	//	}
	//}
	}
} //end if reverse

## ECHO ZONE
echo replace($replace, file_get_contents($basedir.'/'.$conf['schema']));

mysql_close($mysql_link);

function flip_ip($ip) {
    $rev_arr = explode('.',$ip);
    foreach ($rev_arr as $z) {
	if (is_numeric($z) || $z == "0") {
	    $ip_rev = "$z".(!empty($ip_rev) || (isset($ip_rev) && $ip_rev == "0")?".$ip_rev":"");
	}
    }
    return ($ip_rev);
}

function is_valid_domain_name($domain_name)
{
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
	    && preg_match("/^.{1,253}$/", $domain_name) //overall length check
	    && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
}
?>
