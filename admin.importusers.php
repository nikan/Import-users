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
define ("REAL_IMPORT" , 0);
define ("TEST_IMPORT",1);

defined( '_VALID_MOS' ) or
    die( 'Direct Access to this location is not allowed.' );

 // ensure user has access to this function
if (!($acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'all') || $acl->acl_check('administration', 'edit', 'users', $my->usertype, 'components', 'com_dailymessage'))) 
    mosRedirect('index2.php', _NOT_AUTH);
    
 //Declarations
global $path_to_csv;
global $error_type_flag;

//Check the PHP Version to see if $_FILES autoglobal is supported
echo "<br/>PHP Version : ".phpversion()." <br/>" ;
if(version_compare(phpversion(), '4.1.0')==-1)
	echo "Unsuitable PHP version. This programs runs on PHP greater than 4.1.0<br/>";
else 
	echo "PHP Version OK <br/>";


//Check whether uploading files is permitted in this server
	if(get_cfg_var('file_uploads')!=1)
	{
		echo "Uploads are not alowed in this environment. Modify ph.ini to allow uploads. Execution will stop.<br/>";
		exit;
	}
	else 
	{
			echo "Uploads are  allowed in this environment<br/>";
	}
	
// include support libraries
require_once( $mainframe->getPath( 'admin_html' ) );
require_once($mosConfig_absolute_path .'/administrator/components/com_importusers/importconfig.php');
//Store file location for use in Class


//Check whether a file was uploaded
 if(isset($_FILES['csvfile']['tmp_name']))
	{
		$path_to_csv=$mosConfig_absolute_path.'/'.$_FILES['csvfile']['name'];
//Check if the uploaded file is a CSV file
		echo "File type: ". substr($_FILES['csvfile']['name'], -3)."<br/>";
		if(substr($_FILES['csvfile']['name'], -3)=='csv')
		{
				if(move_uploaded_file($_FILES['csvfile']['tmp_name'],$path_to_csv))
					{
						echo "Upload Success. <br/>";
					}
		}
		else 
		{
			if($task != 'cancel')
				{
				echo "You have submitted a non CSV file. This won't work. Please submit a proper CSV file.<br/>";
				$error_type_flag=true;
				}
		}
			
	}	
	
else
	{
		echo "No file uploaded yet<br/>";
	}


//for debuging
//var_dump($_FILES);



// handle the task
$task = mosGetParam( $_REQUEST, 'task', '' );
global $content;
if(!$error_type_flag)
{
	switch ($task) {
		
			case 'new':
			case 'cancel':
			importScreens::init('new');
			break;
			case 'edit_source':
				$file = $mosConfig_absolute_path .'/administrator/components/com_importusers/tmpl/mail.html';
				if ( $fp = fopen( $file, 'r' ) ) {
					$content = fread( $fp, filesize( $file ));
				}
				fclose($fp);
		    	importScreens::editHTML($file, $content);
	      		break;
	      	case 'edit_config':
				$file = $mosConfig_absolute_path .'/administrator/components/com_importusers/importconfig.php';
				if ( $fp = fopen( $file, 'r' ) ) {
					$content = fread( $fp, filesize( $file ));
				}
				fclose($fp);
		    	importScreens::editPHP($file, $content);
		      	break;
	      	case 'save_mail':
		      	$file = $mosConfig_absolute_path .'/administrator/components/com_importusers/tmpl/mail.html';
				if ( $fp = fopen( $file, 'w' ) ) {
				$content=$_REQUEST['filecontent'];
				fwrite($fp,stripcslashes($content));
				}
				fclose($fp);
				importScreens::init('','new');
				break;
			case 'save_config':
		      	$file = $mosConfig_absolute_path .'/administrator/components/com_importusers/importconfig.php';
				if ( $fp = fopen( $file, 'w' ) ) {
				$content=$_REQUEST['filecontent'];
				fwrite($fp,stripcslashes($content));
				}
				fclose($fp);
				importScreens::init('','new');
		      	break;
			case 'import':
				$db_path_to_csv=UserImport::getPath2Csv();
				global $path_to_csv;
				if($db_path_to_csv==null && $path_to_csv==null)
				{
					//echo "Passed from switch path=null <br/>";
					importScreens::importUsers($task);
					}
				else
					UserImport::import(REAL_IMPORT);
					break;
				
	    	case 'testmode':
	    		if($path_to_csv==null)
					importScreens::importUsers($task);
				else
	         		 UserImport::import(TEST_IMPORT);
	      			 break;
	      	default:
	      		importScreens::init('new');
	      		break;
	}
}
else	
	importScreens::init('new');
	

/*
 * Class that does the main import work 
 */


class UserImport
{
	var $path2csv;
	
	function UserImport()
	{
	
	}
	function getHost()
	{
	//hostname or ip address of the mySQL database Mambo uses.This is usually 'localhost'
	$sql_hostname = $GLOBALS['mosConfig_host']; 
	return $sql_hostname;
	}
	function getDbUser()
	{
	// username Mambo uses to connect to your mySQL database	
	$sql_user = $GLOBALS['mosConfig_user'];
	return $sql_user;
	}         
	function getDbPass()
	{
	// password Mambo uses to connect to your mySQL database
	$sql_password = $GLOBALS['mosConfig_password']; 
	return $sql_password;
	}
	function getDb()
	{
	// name of your Mambo database on the mySQL server
	$mambo_database_name = $GLOBALS['mosConfig_db'];
	return $mambo_database_name;
	}
	function getDbPrefix()
	{
	 $mambo_db_prefix=$GLOBALS['mosConfig_dbprefix'];

	 return $mambo_db_prefix;
	}
	function getDelimiter(){
	// character used to separate fields in your .csv file. This character is usually a comma	
	//$csv_delimiter = ',';	    
	global $delimiter;
	$csv_delimiter = $delimiter;
	return $csv_delimiter;      									 									 									 
	}
	function getPath2Csv()
	{
	//Stored path to the uploaded CSV text file 
	global $connection;
	if($connection==null)
		$connection = mysql_connect(UserImport::getHost(),UserImport::getDbUser(),UserImport::getDbPass()) 
				      or die ('Not connected to Mambo database: ' . mysql_error());
	global $mos_iu_csvfiles;
		$mos_iu_csvfiles=UserImport::getdbPrefix()."iu_csvfiles";
	$path_to_csv=mysql_fetch_row(mysql_query("SELECT name FROM $mos_iu_csvfiles where active='1'", $connection)); 
	//echo "getPath2csv function returns: ". $path_to_csv[0] ."<br/>";
	$return=$path_to_csv[0];
		return $return;
	
	}

	function import($mode)
	{	

		//keeps all output till end of processing 
		$num_records=0;
		global $From, $subject, $path_to_csv;
		
		
				if($mode==REAL_IMPORT) 
					echo "Mode: Actual import<br/>";	
				else
					echo  "Mode: Test import<br/>";
				
				global  $old_id, $new_id;
				
				// Set up connection to Mambo database
				global $connection;
				if($connection==null)
						$connection = mysql_connect(UserImport::getHost(),UserImport::getDbUser(),UserImport::getDbPass()) 
				      or die ('Not connected to Mambo database: ' . mysql_error());
				      
					 echo "Connected to Mambo Database at: ". UserImport::getHost(). ".".UserImport::getDb();
				
				mysql_select_db(UserImport::getDb(), $connection) or die ('Can\'t select Mambo database ' .$mambo_database_name . ': ' . mysql_error()); 
				global $mos_users;
				$mos_users=UserImport::getdbPrefix()."users";
				global $mos_core_acl_aro;
				$mos_core_acl_aro=UserImport::getdbPrefix()."core_acl_aro";
				global $mos_core_acl_groups_aro_map;
				$mos_core_acl_groups_aro_map=UserImport::getdbPrefix()."core_acl_groups_aro_map";
				global $mos_iu_csvfiles;
				$mos_iu_csvfiles=UserImport::getdbPrefix()."iu_csvfiles";
				
				// Create new starting "id" number for mos_users table by 
				// getting the current highest id number and adding 1
				$current_id = mysql_query("SELECT MAX(id) FROM $mos_users", $connection);
			 	while ($row = mysql_fetch_array($current_id)) { 
					$old_id = $row[0];
					$new_id = $old_id + 1;   
					echo "<p>Current highest 'id' in ". UserImport::getdbPrefix()."users is:  " . $row[0]  . " <br>The new 'id' numbers will start at: " . $new_id . "<br>" ;
				} 
				//get the highest csv file id 
				$current_file_id=mysql_query("SELECT MAX(id) FROM $mos_iu_csvfiles", $connection);
				//Store the current csvfile
		
				if(UserImport::getPath2Csv()==null){
					$path_to_csv=mysql_real_escape_string($path_to_csv, $connection);
					mysql_query("INSERT INTO $mos_iu_csvfiles (id, name, active)VALUES ('$current_file_id', '$path_to_csv', '1')",$connection)	or die("<br> Mos_iu_csvfiles not updated. Error is: " . mysql_error());
						}
				// open the .csv file
				$handle = fopen (UserImport::getPath2Csv(),"r"); 
				
				
				while ($data = fgetcsv ($handle, 1000, UserImport::getDelimiter())) {
				
					// Here we assign some values for the SQL statements
					// We have to escape the name strings so the SQL doesn't throw errors when it sees 
					// names with embedded quotes like "O'Brien"
					$username = mysql_escape_string($data[0]);
					$name = mysql_escape_string($data[1]);
					$email = $data[2];
					$registerdate = date("Y-m-d H:i:s");	
			/* creates a new password for each user so it can be included in the email*/
					$clearpassword = $username . date("si");
    				$password = md5($clearpassword); 
    				$myRows = mysql_query("SELECT COUNT(*) from $mos_users WHERE username = '$username'", $connection) or die("<br> Error getting Count. Error is: " . mysql_error());
					$dupCountRow = mysql_fetch_row($myRows);
				switch ($mode) { 
				case TEST_IMPORT: 
						$myRows = mysql_query("SELECT COUNT(*) from $mos_users WHERE username = '$username'", $connection) or die("<br> Error getting Count. Error is: " . mysql_error());
						$dupCountRow = mysql_fetch_row($myRows);
						// DUP Check
						if ($dupCountRow[0] > 0) 
							{
								echo  "User Already exists : $username<br>";
							} 
						else 
							{
								echo "<br> In testing mode: User " . $name . " would have been added to Mambo CMS";
								$num_records++;
							}
					break;
				case REAL_IMPORT: 
					// - first check if user exist
							$myRows = mysql_query("SELECT COUNT(*) from $mos_users WHERE username = '$username'", $connection) or die("<br> Error getting Count. Error is: " . mysql_error());
			
							$dupCountRow = mysql_fetch_row($myRows);
				
							// DUP Check
							if ($dupCountRow[0] > 0) {
								echo  "User Already exists : $username<br>";
													} 
							else 
							{

								// mysql_query("INSERT ...");
								// This is where the existing script INSERT stmts and such go, don't forget to close the IF 				
								mysql_query("INSERT INTO $mos_users (id, name, username, email, password , usertype, block, sendEmail, gid, registerDate, lastvisitDate)
								VALUES ('$new_id', '$name', '$username', '$email', '$password', 'registered', '0', '0', '18', '$registerdate', '0000-00-00 00:00:00')",$connection) 
								or die("<br> Mos_users not updated. Error is: " . mysql_error());
								mysql_query("INSERT INTO $mos_core_acl_aro (aro_id , section_value , value , order_value , name , hidden) 
								VALUES ('$new_id', 'users', '$new_id', '0', '$name', '0')",$connection) 
								or die("<br> Mos_core_acl_aro not updated. Error is: " . mysql_error());	
								mysql_query("INSERT INTO $mos_core_acl_groups_aro_map (group_id , section_value , aro_id) 
								VALUES ('18', '', '$new_id')",$connection) or die("<br> Mos_core_acl_groups_aro_map not updated. Error is: " . mysql_error());
									
							
					
									echo"<br> User " . $name . " added to Mambo CMS";
									$num_records++;
					//TODO: this needs to be changed to mambo api mail calls
									// Beginning of code to send confirmation email
									/* recipient */
									$to = "$email"; 
									
									/* subject */
									//$subject = "Your account in our website";
									
									//This is the email - Insert what you want to say below - Title tags is the subject field on the email//
									/* message */
									$message = importScreens::notification();
									$message=str_replace('{NAME}',$name,$message);
									$message=str_replace('{USERNAME}',$username,$message);
									$message=str_replace('{PASSWORD}',$clearpassword,$message);
									/* To send HTML mail, you can set the Content-type header. */
									$headers = "MIME-Version: 1.0\r\n";
									$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
									
									/* additional headers - Enter who the email is from and email address*/
									$headers .= "To: " . $name . " <" . $email . ">\r\n";
									$headers .= "From:".$From ."\r\n";
									/* and now mail it */
									mail($to, $subject, $message, $headers);
									// End of of code to send a confirmation email
								} // end else - username didn't exist in database so we wrote a new record
								break;
					default: 
						echo "<br> \$mode variable not set properly: User " . $name . " would have been added to Mambo CMS";
				
					} // end switch ($mode)

						
	

		
				// increment id counter
				$new_id++;
	
		} // end while ($data = fgetcsv ($handle, 1000, $csv_delimiter))
 
		fclose ($handle);
		if($mode==REAL_IMPORT)
		{
			mysql_query("UPDATE $mos_iu_csvfiles SET active='0' 
								WHERE active='1'", $connection) or  die("<br> Mos_iu_csvfiles not updated. Error is: " . mysql_error());
		//echo "Database csv name updated <br/>";	
		//$num_records = $new_id - $old_id - 1;
			echo "<br/>".$num_records . " users added to the Mambo Database";
		}	
		else
					echo  "<br/>".$num_records . " users would have been added to the Mambo Database";
		mysql_close($connection);
		echo "<br/><br/>";
		echo "<br> Processing complete at: " .  "$registerdate";
		importScreens::init('new');
		}	
}
?>