<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

t3lib_div::loadTCA('tt_content');

$TCA["tx_veguestbook_entries"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries",		
		"label" => "entry",	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"default_sortby" => "ORDER BY crdate DESC",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."ext_icon.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, sys_language_uid, firstname, surname, email, homepage, place, entry, entrycomment, remote_addr",
	)
);


$TCA['tt_content']['types']['list']['subtypes_excludelist']['ve_guestbook_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist']['ve_guestbook_pi1']='pi_flexform';

t3lib_extMgm::addPlugin(Array('LLL:EXT:ve_guestbook/locallang_tca.php:ve_guestbook', 've_guestbook_pi1'));
t3lib_extMgm::addPiFlexFormValue('ve_guestbook_pi1', 'FILE:EXT:ve_guestbook/flexform_ds.xml');

t3lib_extMgm::allowTableOnStandardPages("tx_veguestbook_entries");
t3lib_extMgm::addToInsertRecords('tx_veguestbook_entries');


if (TYPO3_MODE=='BE')	{
	// Adds wizard icon to the content element wizard.
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_veguestbook_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_veguestbook_pi1_wizicon.php';
	
}
?>