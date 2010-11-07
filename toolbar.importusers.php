<?php
/*
 * Created on 3 Οκτ 2005
 * @mamboversion 4.5.2 
 * @version 0.3
 * @package ImportUsers
 * @copyright (C) 2005 Nikos Anagnostou
 * @copyright (C) for the import part of the code Wayne Stewart
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/
/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or
    die( 'Direct Access to this location is not allowed.' );
 
 // ensure user has access to this function
if (!($acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'all') || $acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'com_dailymessage'))) {
    mosRedirect('index2.php', _NOT_AUTH);
}

// include support libraries
require_once( $mainframe->getPath( 'toolbar_html' ) );
 
// handle the task
$task = mosGetParam( $_REQUEST, 'task', '' );

switch ($task) {
		case 'new':
		case 'import':
		case 'testmode':
		importToolbar::importUsers();
		break;
		case 'edit_source':
		importToolbar::editMail();
		break;
		case 'edit_config':
		importToolbar::editConfig();
		break;
      default:
         importToolbar::_DEFAULT();
        break;
}
?>