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
 * @subpackage	Reseller
 * @copyright   2001-2006 by moleSoftware GmbH
 * @copyright   2006-2010 by ispCP | http://isp-control.net
 * @copyright   2010-2012 by i-MSCP | http://i-mscp.net
 * @author      ispCP Team
 * @author      i-MSCP Team
 * @link        http://i-mscp.net
 */

/************************************************************************************
 * Main script
 */

// Include core library
require 'imscp-lib.php';

iMSCP_Events_Manager::getInstance()->dispatch(iMSCP_Events::onResellerScriptStart);

check_login(__FILE__);

/** @var $cfg abook_local_file */
$cfg = iMSCP_Registry::get('config');

$userId = $_SESSION['user_id'];
$_SESSION['previousPage'] = 'ticket_system.php';

// Checks if support ticket system is activated and if the reseller can access to it
if (!hasTicketSystem($userId)) {
    redirectTo('index.php');
} elseif(isset($_GET['ticket_id']) && !empty($_GET['ticket_id'])) {
    closeTicket((int) $_GET['ticket_id']);
}

if (isset($_GET['psi'])) {
    $start = $_GET['psi'];
} else {
    $start = 0;
}

$tpl = new iMSCP_pTemplate();
$tpl->define_dynamic(
	array(
		'layout' => 'shared/layouts/ui.tpl',
		'page' => 'reseller/ticket_system.tpl',
		'page_message' => 'layout',
		'tickets_list' => 'page',
		'tickets_item' => 'tickets_list',
		'scroll_prev_gray' => 'page',
		'scroll_prev' => 'page',
		'scroll_next_gray' => 'page',
		'scroll_next' => 'page'));

$tpl->assign(
	array(
		'THEME_CHARSET' => tr('encoding'),
		'TR_PAGE_TITLE' => tr('i-MSCP - Reseller / Support Ticket System / Open Tickets'),
		'ISP_LOGO' => layout_getUserLogo(),
		'TR_SUPPORT_SYSTEM' => tr('Support Ticket System'),
		'TR_OPEN_TICKETS' => tr('Open tickets'),
		'TR_CLOSED_TICKETS' => tr('Closed tickets'),
		'TR_TICKET_STATUS' => tr('Status'),
		'TR_TICKET_FROM' => tr('From'),
		'TR_TICKET_SUBJECT' => tr('Subject'),
		'TR_TICKET_URGENCY' => tr('Priority'),
		'TR_TICKET_LAST_ANSWER_DATE' => tr('Last reply date'),
		'TR_TICKET_ACTIONS' => tr('Actions'),
		'TR_TICKET_DELETE' => tr('Delete'),
		'TR_TICKET_CLOSE' => tr('Close'),
		'TR_TICKET_READ_LINK' => tr('Read the ticket'),
		'TR_TICKET_DELETE_LINK' => tr('Delete the ticket'),
		'TR_TICKET_CLOSE_LINK' => tr('Close the ticket'),
		'TR_TICKET_DELETE_ALL' => tr('Delete all tickets'),
		'TR_TICKETS_DELETE_MESSAGE' => tr("Are you sure you want to delete the '%s' ticket?", '%s'),
		'TR_TICKETS_DELETE_ALL_MESSAGE' => tr('Are you sure you want to delete all tickets?'),
		'TR_PREVIOUS' => tr('Previous'),
		'TR_NEXT' => tr('Next')));

generateNavigation($tpl);
generateTicketList($tpl, $userId, $start, $cfg->DOMAIN_ROWS_PER_PAGE, 'reseller', 'open');
generatePageMessage($tpl);

$tpl->parse('LAYOUT_CONTENT', 'page');

iMSCP_Events_Manager::getInstance()->dispatch(iMSCP_Events::onResellerScriptEnd, array('templateEngine' => $tpl));

$tpl->prnt();
unsetMessages();
