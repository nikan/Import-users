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
 /* ensure user has access to this function*/
if (!($acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'all') || $acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'com_dailymessage'))) 
    mosRedirect('index2.php', _NOT_AUTH);

/*
 * 
 * 
 */
class importScreens {
	
/*
* 
*/   
    function init($task)
    {
	//	if(UserImport::getPath2Csv()==null)
			echo '<form method="POST" action="index2.php" name="adminForm">' .
					'<input type="hidden" name="boxchecked" value="1"  />' .
					'<input type="hidden" name="option" value="com_importusers"  />' .
					'<input type="hidden" name="task" value="'.$task.'"/></form>';
    }

/**
 * 
 */
    function importUsers($task) {
 
    	$csv=UserImport::getPath2Csv();
       	if(empty($csv))
    	{
			echo '<form enctype="multipart/form-data" ' .
					'action="index2.php" method="POST" ' .
					'name="adminForm"><input type="hidden" ' .
					'name="MAX_FILE_SIZE" value="3000000"/>' .
					'<input name="csvfile" type="file" ' .
					'title="CSV File to import"/><input type="hidden" ' .
					'name="option" value="com_importusers" />' .
					'<input type="hidden" name="task" value="'.$task.'" />' .
							'<input type="submit" value="Upload+Process"/></form>';
    	}
    	else
    		{
    		echo "Using csv uploaded before: $csv"."<br/>";
    					echo '<form  ' .
					'action="index2.php" method="POST" ' .
					'name="adminForm"><input type="hidden" ' .
					'name="option" value="com_importusers" />' .
					'<input type="hidden" name="task" value="'.$task.'" />' ;
    		}
    }


/*
 * 
 */    
    
        function notification()
    {
    	global  $mosConfig_absolute_path;
    	$file = $mosConfig_absolute_path .'/administrator/components/com_importusers/tmpl/mail.html';
		if ( $fp = fopen( $file, 'r' ) ) {
			$content = fread( $fp, filesize( $file ) );
		}
		else
    		echo "Mail template not found or could not be opened <br/>";
		fclose($fp);
		
    	return $content;   		

    }


/**
  *
  */
function editHTML( $file , $content) 
{
		global $mosConfig_absolute_path;
        $template_path = $file;
            
		?>
		<form action="index2.php" method="post" name="adminForm">
	    <table cellpadding="1" cellspacing="1" border="0" width="100%">
		<tr>
	        <td width="290"><table class="adminheading"><tr><th class="templates">Mail HTML Editor</th></tr></table></td>

	    </tr>
	    </table>
		<table class="adminform">
	        <tr><th><?php echo $template_path; ?></th></tr>
	        <tr><td><textarea style="width:100%;height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $content; ?></textarea></td></tr>
		</table>
		<input type="hidden" name="option" value="com_importusers" />
		<input type="hidden" name="task" value="new" />
		</form>
		<?php
}
/*
 * 
 */

function editPHP( $file , $content) 
{
		global $mosConfig_absolute_path;
        $config_path = $file;
            
		?>
		<form action="index2.php" method="post" name="adminForm">
	    <table cellpadding="1" cellspacing="1" border="0" width="100%">
		<tr>
	        <td width="290"><table class="adminheading"><tr><th class="templates">Configuration Editor</th></tr></table></td>

	    </tr>
	    </table>
		<table class="adminform">
	        <tr><th><?php echo $config_path; ?></th></tr>
	        <tr><td><textarea style="width:100%;height:500px" cols="110" rows="25" name="filecontent" class="inputbox"><?php echo $content; ?></textarea></td></tr>
		</table>
		<input type="hidden" name="option" value="com_importusers" />
		<input type="hidden" name="task" value="new" />
		</form>
		<?php
}
}
?>

