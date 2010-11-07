<?php
/*
 * Created on 3 Ïêô 2005
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
if (!($acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'all') || $acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'com_dailymessage'))) 
    mosRedirect('index2.php', _NOT_AUTH);
    
class importToolbar {
    /**
     * Displays toolbar
     * 
     */
     
         function _DEFAULT(){
        mosMenuBar::startTable();
        mosMenuBar::addNew('new');
        mosMenuBar::spacer();
        mosMenuBar::editHTML('edit_source','Edit Mail');
        mosMenuBar::spacer();
        mosMenuBar::editHTML('edit_config','Edit Config');
        mosMenuBar::spacer();
        mosMenuBar::help('importusers.html', true );
        mosMenuBar::endTable();
         }
        /*
         * Display import modes
         */
    function importUsers(){
        mosMenuBar::startTable();
        mosMenuBar::custom('import', 'apply.png', 'apply_f2.png','Import', false );
        mosMenuBar::spacer();
        mosMenuBar::custom('testmode', 'apply.png', 'apply_f2.png','Test', false );
        mosMenuBar::spacer();
        mosMenuBar::cancel('cancel');
        mosMenuBar::endTable();
    }
/*
 * Display editing tools
 */
	function editMail(){
		mosMenuBar::startTable();
        mosMenuBar::save('save_mail', 'Save Mail');
        mosMenuBar::spacer();
        mosMenuBar::cancel('cancel');
        mosMenuBar::endTable();
    }
    
    	function editConfig(){
		mosMenuBar::startTable();
        mosMenuBar::save('save_config', 'Save Config');
        mosMenuBar::spacer();
        mosMenuBar::cancel('cancel');
        mosMenuBar::endTable();
    }

}

?>
