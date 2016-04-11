<?php
/**
 * hardcodeeditor.php
 * 
 * the smalest content editor
 *
 * just put comments: <!-- EDITABLE AREA --> and <!-- END EDITABLE AREA --> into your document to make it editable
 *
 * @version 1.2
 * @copyright 2006 by Rusty Zotin, www.zotin.com
 **/
 
 /**
  * ####################################################################
  * configuration
  * PLEASE CHANGE YOUR ADMIN PASSWORD!!
  */
  $admin_password  = "admin";
  $write_backup		= true;
  /**
  * end configuration
  * #################################################################### 
  */
  
 session_start();
?>
<html>
<head>
<style type="text/css">
<!--
  body,
  .text 
  {  
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000033
  }
  .warning
  {
	color: red;
  }
-->
</style>
</head>

<body bgcolor="#E5E5E5">
<h1>HardCodeEditor v1.2</h1>&copy 2006 by rusty <a href="http://zotin.com/blog/2006/05/21/hardcodeeditor/" target="_blank">zotin</a>
<br />
<?php
 // Authentifikation
 if (isset($_POST['pw']) && $_POST['pw'] == $admin_password)
 {
   $_SESSION['auth'] = $_POST['pw'];
 }
 else
 if (!isset($_SESSION['auth']) || $_SESSION['auth'] != $admin_password)
 {
   echo 'Authentifikation failed. Please login:&nbsp;'."\n";
   echo '<form name="login" action="'.$_SERVER['PHP_SELF'].'" method="POST"><input type="text" name="pw" /><input type="submit" name="submit" value="login"></form>';
   die('</body></html>');
 }
?>

<form name="editpage" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table cellpadding="" cellspacing="" border="0" class="text">
<tr>
  <td colspan="3">edit file:</td>
</tr>
<tr>
  <td>http://<?php echo $_SERVER['SERVER_NAME']; ?>/</td>
  <td><input type="text" class="text" size="25" name="site" value="<?php echo isset($_POST['site']) ? $_POST['site']: "" ?>"></td>
  <td><input type="submit" class="text" name="submit" value="edit"></td>
</tr>
</table>
</form>
<font color="#E5E5E5">
<?php
  if (
    (
      isset($_POST['submit'])
      &&
      isset($_POST['site'])
      &&
      "edit" == $_POST['submit'] 
      &&
      "" != trim($_POST['site'])
    ) 
    || 
      isset($_GET['site'])
    )
  {
  // parse site & show form
     if (
         is_file($_SERVER['DOCUMENT_ROOT']."/".$_POST['site'])
         &&
         !ereg("hardcodeeditor.php",$_POST['site'])
       )
     {

    if ($handle = fopen ($_SERVER['DOCUMENT_ROOT']."/".$_POST['site'], "r"))
    {
      // read file contents
        $buffer = "";
      while (!feof($handle)) {
          $buffer .= fgets($handle, 4096);
      }
      fclose ($handle);  
      
      $first_split = explode("<!-- EDITABLE AREA -->", $buffer);
      if (count($first_split) < 2) {
          echo 'There are no EDITABLE AREAS set in this file.<br />';
      } else {
        $temp_content[0] = $first_split[0];
        for ($i = 1; $i < count($first_split); $i++)
        {
          $second_split			= explode("<!-- END EDITABLE AREA -->", $first_split[$i]);
          $temp_content[$i]		= $second_split[1];
          $temp_editable[($i-1)]	= $second_split[0];
        }
        echo '<form name="savepage" action="'.$_SERVER['PHP_SELF'].'" method="post">'."\n";
        echo '<input type="hidden" name="site" value="'.$_POST['site'].'">'."\n";
        for ($j = 0; $j < count($temp_editable); $j++)
        {
          echo '<textarea rows="30" cols="100" name="textfield[]">'.$temp_editable[$j].'</textarea><br />'."\n";
        }
        echo '<br><input type="submit" class="text" name="submit" value="save">'."\n";
        echo '</form>'."\n";
      }
    }
    else
    {
      echo '<font class="warning"><b>Could not open file for reading.</b></font><br />'."\n";
    }
   }
   else
   { // end  if (is_file("/".$_POST['site'])) {
    echo '<font class="warning"><b>This file does not exist!</b></font><br />'."\n";
   }
    
  } 
  else 
  if (
    isset($_POST['submit'])
    &&
    "save" == $_POST['submit'] 
    ) 
  {
       // saving document
    if ($handle = fopen ($_SERVER['DOCUMENT_ROOT']."/".$_POST['site'], "r"))
    {
        $buffer = "";
      while (!feof($handle))
      {
          $buffer.= fgets($handle, 4096);
      }
      fclose ($handle); 
    }
      $first_split = explode("<!-- EDITABLE AREA -->", $buffer);
  
      $buffer = $first_split[0];
      for ($i = 1; $i < count($first_split); $i++)
      {
        $second_split	= explode("<!-- END EDITABLE AREA -->", $first_split[$i]);
        $buffer			.= "<!-- EDITABLE AREA -->".preg_replace("/''/",'"',stripcslashes ($_POST['textfield'][($i-1)]))."<!-- END EDITABLE AREA -->";
        $buffer			.= $second_split[1];
      }
    if ($handle = fopen ($_SERVER['DOCUMENT_ROOT']."/".$_POST['site'], "w"))
    {

        if (!fwrite($handle, $buffer))
        {
            echo 'File '.$_SERVER['SERVER_NAME'].'/'.$_POST['site'].' ist not writable. Check its rights. (chmod 660)';
            exit;
        } else {
        echo '<font color="green">File http://'.$_SERVER['SERVER_NAME'].'/'.$_POST['site'].' saved.</font><br />
        <br /><a href="http://'.$_SERVER['SERVER_NAME'].'/'.$_POST['site'].'">show edited document...</a>'."\n";
      }
      fclose ($handle);
      
      // write backupfile
      if ($write_backup)
      {
	      $backup_file 	= explode(".",basename($_POST['site']));
	      $backup_handle 	= fopen ($_SERVER['DOCUMENT_ROOT']."/".dirname($_POST['site'])."/".$backup_file[0]."_bak.".$backup_file[1], "w+");
	      fwrite($backup_handle, $buffer);
	      fclose ($backup_handle);
	      chmod($_SERVER['DOCUMENT_ROOT']."/".dirname($_POST['site'])."/".$backup_file[0]."_bak.".$backup_file[1], 0660);
	      echo '<br /><a href="http://'.$_SERVER['SERVER_NAME']."/".dirname($_POST['site'])."/".$backup_file[0]."_bak.".$backup_file[1].'">show backup document...</a>'."\n";
      }   
      
    }
    else
    {
      echo '<font class="warning"><b>Could not open file '.$_SERVER['SERVER_NAME'].'/'.$_POST['site'].' for writing!</b></font>'."\n";
    }
  }

?>
</font>
<br /><br />
<?php
if ("admin" == $admin_password)
{
	echo '<div class="warning"><b>YOU SHOULD CHANGE YOUR ADMINISTRATION PASSWORD inside hardcodeeditor.php file and upload it again.</b></div><br />'."\n";
}
?>
<i>You are able to edit any of your static HTML files by placing following HTML comments {around the content parts} you want to make editable:<br></i>
&lt;!-- EDITABLE AREA --><br />
<i>und<br></i>
&lt;!-- END EDITABLE AREA --><br />
<i><br />
Your editable file should have following rights, that can be set with a FTP-Client:</i> <b>660</b><br />
After every change you'll find a backup file in the same folder with a postfix _bak.
<br />
<br />
<br />
</body>
</html>
