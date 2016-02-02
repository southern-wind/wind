<?php
/*
 * WiND - Wireless Nodes Database
 * 
 * Copyright (C) 2005-2014 	by WiND Contributors (see AUTHORS.txt)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class to handle SRTM Data
 */

class srtm {

        var $data_path;

function __construct($data_path='') {
                $this->data_path = $data_path;
        }

        public static function get_filename($lat, $lon) {
                $ll = srtm::get_lat_long_adjustments($lat, $lon);
                //print_R($ll);
                $n_lat = ($lat+$ll['lat_adj']);
                $n_lon = ($lon+$ll['lon_adj']);
                return $ll['lat_dir'].sprintf("%02.0f",abs((integer)($lat+$ll['lat_adj'])))
                       .$ll['lon_dir'].sprintf("%03.0f",abs((integer)($lon+$ll['lon_adj']))).'.hgt';
        }

        function get_lat_long_adjustments($lat,$lon) {
                //adjustment for filenames.
                // as the filename is based upon the bottom left corner
                // S locations will be records for -31.0 to -31.9 in a S32 file.
                if ($lat < 0) {
                        $r['lat_dir'] = 'S';
                        $r['lat_adj'] = -1;
                } else {
                        $r['lat_dir'] = 'N';
                        $r['lat_adj'] = 0;
                }
                if ($lon < 0) {
                        $r['lon_dir'] = 'W';
                        $r['lon_adj'] = 0;
                } else {
                        $r['lon_dir'] = 'E';
                        $r['lon_adj'] = 0;
                }
                return $r;
        }

        function get_elevation($lat, $lon, $round=TRUE) {

                $filename = $this->data_path.$this->get_filename($lat,$lon);
                if ($lat === '' || $lon === '' || !file_exists($filename)) return FALSE;

                $file = fopen($filename, 'r');

                $ll = $this->get_lat_long_adjustments($lat,$lon);
                $lat_dir = $ll['lat_dir'];
                $lat_adj = $ll['lat_adj'];
                $lon_dir = $ll['lon_dir'];
                $lon_adj = $ll['lon_adj'];
                $y = $lat;
                $x = $lon;

                /*
                   Exracting data from NASA .hgt files?
                   Here is a description from www2.jpl.nasa.gov/srtm/faq.html:

                 * The SRTM data files have names like "N34W119.hgt". What do the letters and numbers refer to, and what is ".hgt" format?
                 *
                 * Each data file covers a one-degree-of-latitude by one-degree-of-longitude block of Earth's surface.
                 * The first seven characters indicate the southwest corner of the block, with N, S, E, and W referring
                 * to north, south, east, and west. Thus, the "N34W119.hgt" file covers
                 * latitudes 34 to 35 North and
                 * longitudes 118-119 West
                 * (this file includes downtown Los Angeles, California).
                 * The filename extension ".hgt" simply stands for the word "height", meaning elevation.
                 * It is NOT a format type. These files are in "raw" format (no headers and not compressed),
                 * 16-bit signed integers, elevation measured in meters above sea level, in a
                 * "geographic" (latitude and longitude array) projection, with data voids indicated by -32768.
                 *
                 * International 3-arc-second files have 1201 columns and 1201 rows of data, with a total filesize
                 * of 2,884,802 bytes ( = 1201 x 1201 x 2).
                 *
                 * United States 1-arc-second files have 3601 columns and 3601 rows of data, with a total filesize
                 * of 25,934,402 bytes ( = 3601 x 3601 x 2).
                 *
                 * For more information read the text file "SRTM_Topo.txt" at http://dds.cr.usgs.gov/srtm/version1/Documentation/SRTM_Topo.txt

                 *** S-WiND ***
                 Due to the nature of HGT files, we must read the HGT differently depending if the location is
                 North or South of the equator.
                 - We read them as 2 byte binary
                 - check against http://www.heywhatsthat.com/profiler.html

                 A HGT file records elevations within a square.
                 Data is stored from the upper left corner.     -->     +----+
                 							|    |
                 							|    |
                 The File is named from its lower left corner   -->     +----+

                 *** Not implimented until required ***
                 USA has 1-arc-second files (its like HD for HGT files)
                 * These require 3601 rows + colums,
                 * so if it is ever needed, the calcs below need adjusting and testing for that.

                 jammin - 31/1/2016
                 */

                if ($lat_dir == "S") { //South of the equator offset
                	//Round ensures a whole number for pixel
                        $line = (integer)(( (integer)$lat - $lat ) * 1201) * 1201;
                        $pixel = round(($lon - (integer)$lon ) * 1201);

                        $offset = ($line + $pixel)*2;
                } else {  //North of the equator offset 
                	/* (un-modified WiND version)
                        $offset = ( (integer)(($x - (integer)$x + $lon_adj) * 1200)
                                        * 2 + (1200 - (integer)(($y - (integer)$y + $lat_adj) * 1200))
                                        * 2402 );
                        */
                        $line = (1200 - (integer)(($y - (integer)$y + $lat_adj) * 1200));
                        $pixel = (integer)(($x - (integer)$x + $lon_adj) * 1200) * 2;
                        $offset = $pixel + $line * 2402;
                }
                
                //Debug
                if (isset($main->userdata->privileges['admin']) && $main->userdata->privileges['admin'] === TRUE && $vars['debug']['enabled'] == TRUE) {
                        echo "DEBUG - SRTM.php - filename:$filename,
                             lat:$lat, lat_dir:$lat_dir
                                     lon:$lon, lon_dir:$lon_dir
                                     Line:$line, pixel:$pixel, offset:$offset<br />\n";
                }

                if ($lat_dir == "S") { //South of the equator Read
                        fseek($file, $offset);
                        //reverse string - file read (handle, length)
                        $h1 = srtm::bytes2int(strrev(fread($file, 2)));
                        if ($h1 == -32768) { //Max depth, make 0
                                $h1 = 0;
                        }
                        fclose ($file);
                        return $h1;
                } else { //North of the equator Read (un-modified WiND Version)
                        fseek($file, $offset);
                        $h1 = srtm::bytes2int(strrev(fread($file, 2)));
                        $h2 = srtm::bytes2int(strrev(fread($file, 2)));
                        fseek($file, $offset-2402);
                        $h3 = srtm::bytes2int(strrev(fread($file, 2)));
                        $h4 = srtm::bytes2int(strrev(fread($file, 2)));
                        fclose($file);

                        $m = max($h1, $h2, $h3, $h4);
                        for($i=1;$i<=4;$i++) {
                                $c = 'h'.$i;
                                if ($$c == -32768)
                                        $$c = $m;
                        }

                        $fx = ($lon - ((integer)($lon * 1200) / 1200)) * 1200;
                        $fy = ($lat - ((integer)($lat * 1200) / 1200)) * 1200;

                        // normalizing data
                        $elevation = ($h1 * (1 - $fx) + $h2 * $fx) * (1 - $fy) + ($h3 * (1 - $fx) + $h4 * $fx) * $fy;
                        if ($round) $elevation = round($elevation);
                        return $elevation;

                } //End else

        } //End get_elevation

        function bytes2int($val) {
                $t = unpack("s", $val);
                $ret = $t[1];
                return $ret;
        }

} //end class


?>
