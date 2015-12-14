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

                //whole number - full number * lines (1201)
                // * (
                $line = (integer)(( (integer)$lat - $lat )* 1201);
                $pixel = round(($lon - (integer)$lon ) * 1201);

                //Debug
                if (isset($_SESSION['userdata']['id']) && $_SESSION['userdata']['id']=="-1") {
                        //echo "filename:$filename, lat:$lat, lon:$lon\n";
                        //echo "Line:$line, pixel:$pixel, iLine:$iLine,
			//offset $offset_no_adj <br />\n";
                }
                //file naming is from lower left corner.
                //data is stored within the file from the upper left corner.
                $offset = (($line*1201) + $pixel)*2;

                fseek($file, $offset);
                //reverse string - file read (handle, length)
                $h1 = bytes2int(strrev(fread($file, 2)));
                if ($h1 == -32768) {
                        $h1 = 0;
                }
                fclose ($file);
                return $h1;
        }

}

function bytes2int($val) {
        $t = unpack("s", $val);
        $ret = $t[1];
        return $ret;
}


?>
