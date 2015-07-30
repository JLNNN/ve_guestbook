<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TCA["tx_veguestbook_entries"] = Array (
	"ctrl" => $TCA["tx_veguestbook_entries"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,sys_language_uid,firstname,surname,email,homepage,place,entry,entrycomment,remote_addr"
	),
	"feInterface" => $TCA["tx_veguestbook_entries"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'crdate' => Array (
			'exclude' => 1,	
			'l10n_mode' => 'mergeIfNotBlank',
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.crdate",	
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'tstamp' => Array (
			'exclude' => 1,	
			'l10n_mode' => 'mergeIfNotBlank',
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.tstamp",	
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		"firstname" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.firstname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "25",	
				"eval" => "trim",
			)
		),
		"surname" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.surname",		
			"config" => Array (
				"type" => "input",	
				"size" => "48",	
				"eval" => "trim",
			)
		),
		"email" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "50",	
				"eval" => "trim",
			)
		),
		"homepage" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.homepage",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "2083",	
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					),
				),
				"eval" => "trim",
			)
		),
		"place" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.place",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "50",	
				"eval" => "trim",
			)
		),
		"entry" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.entry",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"entrycomment" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.entrycomment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"remote_addr" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.remote_addr",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "50",	
				"eval" => "trim",
			)
		),
		"uid_tt_news" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:ve_guestbook/locallang_db.php:tx_veguestbook_entries.uid_tt_news",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "50",	
				"eval" => "trim",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, sys_language_uid, crdate, tstamp, firstname, surname, email, homepage, place, entry, entrycomment, remote_addr;")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>