<?php
/*******************************************************************************
 * qry.file.php - XML interface to file system
 * xsl = "path/to/stylesheet.xsl"
 * path = absolute path to directory
 *
 * Author Remy Lalanne
 * Copyright (c) 2005,2006,2007 Remy Lalanne
 ******************************************************************************/
session_start();

include_once "service_deprecated.php"; //error_code()
include_once "FileSystem/lib.file.php";

damas_service::init_http();
damas_service::accessGranted();

header('Content-type: application/xml');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0, max-age=0", false);
header("Cache-control: private");
header("Pragma: no-cache");
header("Expires: 0");
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";


$cmd = arg("cmd");
$err = $ERR_NOERROR;
$ret = false;

echo "<!-- generated by ".$_SERVER['SCRIPT_NAME']." -->\n";
if ( arg('xsl') )
	echo '<?xml-stylesheet type="text/xsl" href="' . arg('xsl') . '"?>' . "\n";
echo '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">'."\n";
echo "\t<soap:Header>\n";


if ($err==$ERR_NOERROR){
	if (!$cmd)
		$err = $ERR_COMMAND;
}

if ($err==$ERR_NOERROR){
	if (!arg("path"))
		$err = $ERR_FILE_NOT_FOUND;
}

if ($err==$ERR_NOERROR){
	switch ($cmd){
		case "sha1":
			$ret = file_sha1($assetsLCL.arg("path"));
			if (!$ret)
				$err = $ERR_FILE_NOT_FOUND;
			break;
		case "df":
			if (!file_exists($assetsLCL.arg("path")))
				$err = $ERR_FILE_NOT_FOUND;
			$ret = df($assetsLCL.arg("path"));
			break;
		case "diskfreespace":
			$ret = disk_free_space($assetsLCL.arg("path"));
			break;
		case "disktotalspace":
			$ret = disk_total_space($assetsLCL.arg("path"));
			break;
		case "single":
			$ret = filesingle($assetsLCL.arg("path"), $assetsLCL);
			if (!$ret)
				$err = $ERR_FILE_NOT_FOUND;
			break;
		case "single_sha1":
			$ret = filesingle_sha1($assetsLCL.arg("path"), $assetsLCL);
			if (!$ret)
				$err = $ERR_FILE_NOT_FOUND;
			break;
		case "list":
			if (!file_exists($assetsLCL.arg("path")))
				$err = $ERR_FILE_NOT_FOUND;
			$ret = filelist($assetsLCL.arg("path"), $assetsLCL);
			//if (!$ret)
				//$err = $ERR_FILE_EMPTYDIR;
			break;
		case "mkdir":
			if ( !is_writable($assetsLCL.arg("path")) ){
				$err = $ERR_FILE_PERMISSION;
				break;
			}
			if ( file_exists($assetsLCL.arg("path")) ){
				$err = $ERR_FILE_EXISTS;
				break;
			}
			mkdir($assetsLCL.arg("path"));
		case "copyfiles":
			if (!in_array(auth_get_class(),array("admin", "supervisor","dirprod"))){ $err = $ERR_PERMISSION; break; }
		
			if(!file_exists($assetsLCL.arg("path"))){ $err = $ERR_FILE_NOT_FOUND; break; }
		
			if(!file_exists($assetsLCL.arg("target")))
			{
				$ret = createDirs($assetsLCL.arg("target"));
				if(!$ret){ $err = $ERR_FILE_PERMISSION; break; }
			}

			$ret = deleteFiles($assetsLCL.arg("target"));
			if(!$ret){ $err = $ERR_FILE_PERMISSION; break; }

			$ret = copyFiles($assetsLCL.arg("path"),$assetsLCL.arg("target"));
			if(!$ret){ $err = $ERR_FILE_PERMISSION; break; }
			
			break;
		case "spider":
			$ret = spider($assetsLCL.arg("path"), $assetsLCL);
			if (!$ret)
				$err = $ERR_FILE_NOT_FOUND;
			break;
		case "touch":
			if (!in_array(auth_get_class(),array("admin", "supervisor","dirprod"))){ $err = $ERR_PERMISSION; break; }
			$ret = touch($assetsLCL.arg("path"));
			if (!$ret)
				$err = $ERR_FILE_PERMISSION;
			break;
		#case "rm":
		#case "mv":
		#case "mkdir":
		#case "touch":
		#case "chmod":
		#case "chgroup":
		case "fileSave":
			$ret = asset_save(arg("id"), arg("path"), arg("comment"));
			if (!$ret)
				$err = $ERR_ASSET_UPDATE;
			break;
		case "fileUpload":
			$path = $_FILES['file']['tmp_name'];
			if(!is_uploaded_file($path))
				$err = $ERR_FILE_UPLOAD;
			else {
				if(!move_uploaded_file($path, $assetsLCL.arg("path")."/".utf8_encode($_FILES['file']['name'])))
				//if(!move_uploaded_file($path, $assetsLCL.arg("path")."/".$_FILES['file']['name']))
					$err = $ERR_FILE_UPLOAD;
			}
			break;
		case "fileUpload2":
			include_once "../App/damas-xml.php";
			$path = $_FILES['file']['tmp_name'];
			if(!is_uploaded_file($path))
				$err = $ERR_ASSET_UPDATE;
			else{
				if(!move_uploaded_file($path, $assetsLCL.arg("path")."/".$_FILES['file']['name']))
					$err = $ERR_ASSET_UPDATE;
				else{
					//$new_id = model::createNode( $id, "dam:file", $_FILES['file']['name'] );
					$new_id = model::createNode( $id, "dam:file" );
					model::setkey( $new_id, 'name', $_FILES['file']['name'] );
				}
			}
			break;
		default:
			$err = $ERR_COMMAND;
	}
}

echo soaplike_head($cmd,$err);
echo "\t</soap:Header>\n";
echo "\t<soap:Body>\n";
echo "\t\t<returnvalue>$ret</returnvalue>\n";
echo "\t</soap:Body>\n";
echo "</soap:Envelope>\n";
?>
