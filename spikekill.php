<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2016 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

include('./include/auth.php');

$debug = false;

switch(get_nfilter_request_var('method')) {
	case 'stddev':
	case 'variance':
		break;
	default:
		echo "FATAL: SpikeKill method '" . get_nfilter_request_var('method') . "' is Invalid\n";
		exit(1);
		break;
}

if (is_realm_allowed(1043)) {
	$local_data_ids = db_fetch_assoc('SELECT DISTINCT
		data_template_rrd.local_data_id
		FROM graph_templates_item
		LEFT JOIN data_template_rrd 
		ON (graph_templates_item.task_item_id=data_template_rrd.id)
		WHERE graph_templates_item.local_graph_id=' . get_filter_request_var('local_graph_id'));

	$results = '';
	if (sizeof($local_data_ids)) {
		foreach($local_data_ids as $local_data_id) {
			$data_source_path = get_data_source_path($local_data_id['local_data_id'], true);

			if (strlen($data_source_path)) {
				if ($debug) {
					cacti_log(read_config_option('path_php_binary') . ' -q ' . $config['base_path'] . '/cli/removespikes.php ' .
						' -R=' . $data_source_path . (isset_request_var('dryrun') ? ' --dryrun' : '') .
						' -M=' . get_nfilter_request_var('method') .
						' -U=' . $_SESSION['sess_user_id'] . 
						' --html', false);
				}

				$results .= shell_exec(read_config_option('path_php_binary') . ' -q ' . $config['base_path'] . '/cli/removespikes.php ' .
					' -R=' . $data_source_path . (isset_request_var('dryrun') ? ' --dryrun' : '') .
					' -M=' . get_nfilter_request_var('method') .
					' -U=' . $_SESSION['sess_user_id'] . 
					' --html');
			}
		}
	}

	print json_encode(array('local_graph_id' => get_request_var('local_graph_id'), 'results' => $results));
}else{
	echo "FATAL: SpikeKill Not Allowed\n";
}

