<?php
/**
 * i-MSCP a internet Multi Server Control Panel
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "VHCS - Virtual Hosting Control System".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2012 by
 * i-MSCP a internet Multi Server Control Panel. All Rights Reserved.
 *
 * @category	i-MSCP
 * @package		iMSCP_Core
 * @subpackage	Admin
 * @copyright   2001-2006 by moleSoftware GmbH
 * @copyright   2006-2010 by ispCP | http://isp-control.net
 * @copyright   2010-2012 by i-MSCP | http://i-mscp.net
 * @author	  ispCP Team
 * @author	  i-MSCP Team
 * @link		http://i-mscp.net
 */

/******************************************************************************
 * Script functions
 */

/**
 * Generates page.
 *
 * @param iMSCP_pTemplate $tpl Template engine instance
 * @param int $year
 * @param int $month
 * @param int $day
 */
function admin_generatePage($tpl, $year, $month, $day)
{
	$all = array_fill(0, 8, 0);
	$allOtherIn = $allOtherOut = 0;
	$ftm = mktime(0, 0, 0, $month, $day, $year);
	$ltm = mktime(23, 59, 59, $month, $day, $year);

	$query = "SELECT COUNT(`bytes_in`) `cnt` FROM `server_traffic` WHERE `traff_time` > ? AND `traff_time` < ?";
	$stmt = exec_query($query, array($ftm, $ltm));

	$dnum = $stmt->fields['cnt'];

	$query = "
		SELECT
			`traff_time` `ttime`, `bytes_in` `sbin`, `bytes_out` `sbout`, `bytes_mail_in` `smbin`,
			`bytes_mail_out` `smbout`, `bytes_pop_in` `spbin`, `bytes_pop_out` `spbout`, `bytes_web_in` `swbin`,
			`bytes_web_out` `swbout`
		FROM
			`server_traffic`
		WHERE
			`traff_time` > ? AND `traff_time` < ?
	";
	$stmt = exec_query($query, array($ftm, $ltm));

	if ($dnum) {
		for ($i = 0; $i < $dnum; $i++) {
			// make it in kb mb or bytes :)
			$ttime = date('H:i', $stmt->fields['ttime']);

			// make other traffic
			$otherIn = $stmt->fields['sbin'] - ($stmt->fields['swbin'] + $stmt->fields['smbin'] + $stmt->fields['spbin']);
			$otherOut = $stmt->fields['sbout'] - ($stmt->fields['swbout'] + $stmt->fields['smbout'] + $stmt->fields['spbout']);

			$tpl->assign(
				array(
					'HOUR' => $ttime,
					'WEB_IN' => numberBytesHuman($stmt->fields['swbin']),
					'WEB_OUT' => numberBytesHuman($stmt->fields['swbout']),
					'SMTP_IN' => numberBytesHuman($stmt->fields['smbin']),
					'SMTP_OUT' => numberBytesHuman($stmt->fields['smbout']),
					'POP_IN' => numberBytesHuman($stmt->fields['spbin']),
					'POP_OUT' => numberBytesHuman($stmt->fields['spbout']),
					'OTHER_IN' => numberBytesHuman($otherIn),
					'OTHER_OUT' => numberBytesHuman($otherOut),
					'ALL_IN' => numberBytesHuman($stmt->fields['sbin']),
					'ALL_OUT' => numberBytesHuman($stmt->fields['sbout']),
					'ALL' => numberBytesHuman($stmt->fields['sbin'] + $stmt->fields['sbout']),));

			$all[0] = $all[0] + $stmt->fields['swbin'];
			$all[1] = $all[1] + $stmt->fields['swbout'];
			$all[2] = $all[2] + $stmt->fields['smbin'];
			$all[3] = $all[3] + $stmt->fields['smbout'];
			$all[4] = $all[4] + $stmt->fields['spbin'];
			$all[5] = $all[5] + $stmt->fields['spbout'];
			$all[6] = $all[6] + $stmt->fields['sbin'];
			$all[7] = $all[7] + $stmt->fields['sbout'];

			$tpl->parse('HOUR_LIST', '.hour_list');
			$stmt->moveNext();
		}

		$allOtherIn = $all[6] - ($all[0] + $all[2] + $all[4]);
		$allOtherOut = $all[7] - ($all[1] + $all[3] + $all[5]);
	} else {
		$tpl->assign('HOUR_LIST', '');
	}

	$tpl->assign(
		array(
			'WEB_IN_ALL' => numberBytesHuman($all[0]),
			'WEB_OUT_ALL' => numberBytesHuman($all[1]),
			'SMTP_IN_ALL' => numberBytesHuman($all[2]),
			'SMTP_OUT_ALL' => numberBytesHuman($all[3]),
			'POP_IN_ALL' => numberBytesHuman($all[4]),
			'POP_OUT_ALL' => numberBytesHuman($all[5]),
			'OTHER_IN_ALL' => numberBytesHuman($allOtherIn),
			'OTHER_OUT_ALL' => numberBytesHuman($allOtherOut),
			'ALL_IN_ALL' => numberBytesHuman($all[6]),
			'ALL_OUT_ALL' => numberBytesHuman($all[7]),
			'ALL_ALL' => numberBytesHuman($all[6] + $all[7])));
}

/******************************************************************************
 * Main script
 */

// Include core library
require 'imscp-lib.php';

iMSCP_Events_Manager::getInstance()->dispatch(iMSCP_Events::onAdminScriptStart);

check_login(__FILE__);

/** @var $cfg iMSCP_Config_Handler_File */
$cfg = iMSCP_Registry::get('config');

$tpl = new iMSCP_pTemplate();
$tpl->define_dynamic(
	array(
		'layout' => 'shared/layouts/ui.tpl',
		'page' => 'admin/server_statistic_day.tpl',
		'page_message' => 'layout',
		'hour_list' => 'page'));

//global $month, $year, $day;

if (isset($_GET['month']) && isset($_GET['year']) && isset($_GET['day']) && is_numeric($_GET['month']) &&
	is_numeric($_GET['year']) && is_numeric($_GET['day'])
) {
	$year = $_GET['year'];
	$month = $_GET['month'];
	$day = $_GET['day'];
} else {
	set_page_message(tr('Wrong request.'), 'error');
	redirectTo('server_statistic.php');
	exit; // Useless but avoid IDE warning about possible undefined variables
}

$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('i-MSCP - Admin / Server statistics by day'),
		'THEME_CHARSET' => tr('encoding'),
		'ISP_LOGO' => layout_getUserLogo(),
		'TR_MONTH' => tr('Month:'),
		'TR_YEAR' => tr('Year:'),
		'TR_DAY' => tr('Day:'),
		'TR_HOUR' => tr('Hour'),
		'TR_WEB_IN' => tr('Web in'),
		'TR_WEB_OUT' => tr('Web out'),
		'TR_SMTP_IN' => tr('SMTP in'),
		'TR_SMTP_OUT' => tr('SMTP out'),
		'TR_POP_IN' => tr('POP3/IMAP in'),
		'TR_POP_OUT' => tr('POP3/IMAP out'),
		'TR_OTHER_IN' => tr('Other in'),
		'TR_OTHER_OUT' => tr('Other out'),
		'TR_ALL_IN' => tr('All in'),
		'TR_ALL_OUT' => tr('All out'),
		'TR_ALL' => tr('All'),
		'TR_BACK' => tr('Back'),
		'MONTH' => $month,
		'YEAR' => $year,
		'DAY' => $day));

generateNavigation($tpl);
generatePageMessage($tpl);
admin_generatePage($tpl, $year, $month, $day);

$tpl->parse('LAYOUT_CONTENT', 'page');

iMSCP_Events_Manager::getInstance()->dispatch(iMSCP_Events::onAdminScriptEnd, array('templateEngine' => $tpl));

$tpl->prnt();

unsetMessages();
