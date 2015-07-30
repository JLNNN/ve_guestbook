<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_veguestbook_entries=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_veguestbook_entries", field "entry"
	# ***************************************************************************************
RTE.config.tx_veguestbook_entries.entry {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,"editorcfg","
	tt_content.CSS_editor.ch.tx_veguestbook_pi1 = < plugin.tx_veguestbook_pi1.CSS_editor
",43);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_veguestbook_pi1.php', '_pi1', 'list_type', 1);


t3lib_extMgm::addTypoScript($_EXTKEY,"setup","
	tt_content.shortcut.20.0.conf.tx_veguestbook_entries = < plugin.".t3lib_extMgm::getCN($_EXTKEY)."_pi1
	tt_content.shortcut.20.0.conf.tx_veguestbook_entries.CMD = singleView
",43);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ve_guestbook_pi1'][] = 'EXT:ve_guestbook/class.tx_veguestbook_cms_layout.php:tx_veguestbook_cms_layout->getExtensionSummary';
?>