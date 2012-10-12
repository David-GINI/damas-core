<?php
/**
 * Author Remy Lalanne
 * Copyright (c) 2005-2010 Remy Lalanne
 */
session_start();

include_once "service_deprecated.php";
include_once "../php/DAM.php";
include_once "../php/data_model_1.xml.php";
include_once "Workflow/lib.task.php";
include_once "Workflow/workflow.php";

damas_service::init_http();
damas_service::accessGranted();

$err = $ERR_NOERROR;
$cmd = arg("cmd");
$xsl = arg("xsl");
$ret = false;

header('Content-type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo "<!-- generated by ".$_SERVER['SCRIPT_NAME']." -->\n";
if ($xsl)
	echo '<?xml-stylesheet type="text/xsl" href="'.$xsl.'"?>'."\n";
echo '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope">'."\n";
echo "\t<soap:Header>\n";


if ($err==$ERR_NOERROR){
	if (!$cmd)
		$err = $ERR_COMMAND;
}

if ($err==$ERR_NOERROR){
	if (!allowed("libtask.".$cmd))
		$err = $ERR_PERMISSION;
}

if ($err==$ERR_NOERROR){
	switch ($cmd){
		case "taskAdd":
			$ret = model::createNode( arg("id"), "task" );
			model::setKey( $ret, "label", arg("name"));
			if( !$ret ) $err = $ERR_NODE_CREATE;
			break;
		case "taskTag":
			$ret = model::tag(arg("id"), arg("name"));
			if (!$ret) $err = $ERR_NODE_UPDATE;
			break;
		case "taskUntag":
			$ret = model::untag(arg("id"), arg("name"));
			if (!$ret) $err = $ERR_NODE_UPDATE;
			break;
		case "taskSet":
			$ret = model::setKey(arg("id"), arg("name"), arg("value"));
			if (!$ret) $err = $ERR_NODE_UPDATE;
			break;
		case "taskSetTeam":
			$ret = taskSetTeam(arg("id"),arg("value"));
			if (!$ret) $err = $ERR_NODE_UPDATE;
			break;
		case "taskSetState":
			$ret = taskSetState(arg("id"),arg("value"));
			if (!$ret) $err = $ERR_NODE_UPDATE;
			break;
		case "workflowByStateTotal":
			$ret = workflowByStateTotal();
			if (!$ret) $err = $ERR_PERMISSION;
			break;
		case "workflowByState":
			$ret = workflowByState();
			if (!$ret) $err = $ERR_PERMISSION;
			break;
		case "workflowByResource":
			$ret = workflowByResource();
			if (!$ret) $err = $ERR_PERMISSION;
			break;
		case "workflowByTask":
			$ret = workflowByTask(arg("name"));
			if (!$ret) $err = $ERR_PERMISSION;
			break;
		case "workflowByType":
			$ret = workflowByType(arg("name"));
			if (!$ret) $err = $ERR_PERMISSION;
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
