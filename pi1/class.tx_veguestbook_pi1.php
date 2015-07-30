<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2004-2011 Udo von Eynern (udo@voneynern.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Plugin 'guestbook' for the 've_guestbook' extension.
 *
 * @author	Udo von Eynern <udo@voneynern.de>
 * @package TYPO3
 * @subpackage ve_guestbook
 */
class tx_veguestbook_pi1 extends tslib_pibase {
	var $prefixId = "tx_veguestbook_pi1"; // Same as class name
	var $scriptRelPath = "pi1/class.tx_veguestbook_pi1.php"; // Path to this script relative to the extension dir.
	var $extKey = "ve_guestbook"; // The extension key.
	var $strEntryTable = "tx_veguestbook_entries";
	var $code = '';
	var $tt_news = array ();
	var $for_tt_news;
	var $tt_news_url_params_list = 'tx_news_pi1[cat],tx_news_pi1[news],tx_news_pi1[backPid],tx_news_pi1[year],tx_news_pi1[month],tx_news_pi1[day],cHash';

	/**
	 * Init Function: all needed configuration values are stored in the member variable $this->config and the template code goes in $this->templateCode .
	 *
	 * @param	array		$conf: configuration array from TS
	 * @return	void
	 */
	function init($conf) {

		$this->pi_checkCHash = true;

		$this->conf = $conf; // Storing configuration as a member var
		$this->pi_loadLL (); // Loading language-labels
		$this->pi_setPiVarDefaults (); // Set default piVars from TS
		$this->pi_initPIflexForm (); // Init FlexForm configuration for plugin

		if (!is_array ( $this->LOCAL_LANG [$this->LLkey] )) $this->LLkey='default';

		$this->sys_language_uid = $GLOBALS ['TSFE']->config ['config'] ['sys_language_uid'] ? $GLOBALS ['TSFE']->config ['config'] ['sys_language_uid'] : '0';

		$this->enableFields = $this->cObj->enableFields ( $this->strEntryTable );
		$this->code = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'what_to_display', 'sDEF' );

		if ($this->code == 'FORM') {
			$this->pi_USER_INT_obj = 1;
			$GLOBALS ["TSFE"]->set_no_cache ();
		} else {
			$this->pi_USER_INT_obj = 0;
		}

		// Getting the pid list via the flexform
		$pid_list = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'pages', 'sDEF' ) ? implode ( t3lib_div::intExplode ( ',', $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'pages', 'sDEF' ) ), ',' ) : $GLOBALS ['TSFE']->id;

		// Checking for recursive level
		$recursive = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'recursive', 'sDEF' );
		if (is_numeric ( $recursive ) && $recursive > 0) {
			$this->config ['pid_list'] = $this->pi_getPidList ( $pid_list, $recursive );
		} else {
			$this->config ['pid_list'] = $pid_list;
		}

		// Template code
		$templateflex_file = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'template_file', 'sDEF' );

		$this->templateCode = $this->cObj->fileResource ( $templateflex_file ? 'uploads/tx_veguestbook/' . $templateflex_file : $this->conf ['templateFile'] );

		// Redirect page after submitting the form
		$this->config ['redirect_page'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'redirect_page', 's_form' ) ? implode ( t3lib_div::intExplode ( ',', $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'redirect_page', 's_form' ) ), ',' ) : $GLOBALS ['TSFE']->id;

		// CAPTCHA type
		$this->config ['captcha'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'captcha', 's_form' );

		// Obligation fields in the form
		$this->config ['obligationfields'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'obligation_fields', 's_form' );

		if (! empty ( $this->config ['obligationfields'] )) {
			$this->config ['obligationfields'] = explode ( ',', $this->config ['obligationfields'] );
		}

		if (! empty ( $this->conf ['obligationfields'] )) {
			if (is_array ( $this->config ['obligationfields'] ) && count ( $this->config ['obligationfields'] ) > 0) {
				$this->config ['obligationfields'] = array_merge ( explode ( ',', $this->conf ['obligationfields'] ), $this->config ['obligationfields'] );
			} else {
				$this->config ['obligationfields'] = explode ( ',', $this->conf ['obligationfields'] );
			}
		}

		// E-Mail-Validation
		$this->config ['email_validation'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'email_validation', 's_form' );

		// E-Mail-Blacklist
		$this->config ['blacklist_mail'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'blacklist_mail', 's_form' );

		// E-Mail-Whitelist
		$this->config ['whitelist_mail'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'whitelist_mail', 's_form' );

		// E-Mail substitution for spam protection
		$this->config ['email_subst'] = $GLOBALS ['TSFE']->tmpl->setup ['config.'] ['spamProtectEmailAddresses_atSubst'];

		// Website-Validation
		$this->config ['website_validation'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'website_validation', 's_form' );

		// Website-Validation
		$this->config ['strip_tags'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'strip_tags', 's_form' );

		// Website-Validation
		if (! empty ( $this->conf ['allowedTags'] )) {
			$this->config ['allowedTags'] = $this->conf ['allowedTags'];
		} else {
			$this->config ['allowedTags'] = false;
		}

		// Link to the guestbook in teaser mode and notification mail
		$this->config ['guestbook'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'guestbook', 'sDEF' ) ? implode ( t3lib_div::intExplode ( ',', $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'guestbook', 'sDEF' ) ), ',' ) : '';

		if ($this->config ['guestbook'] < 1) {
			$this->config ['guestbook'] = $GLOBALS ["TSFE"]->id;
		}

		// Max items per page shown in list & teaser mode
		$flex_limit = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'limit', 's_list' );

		$this->config ['limit'] = $flex_limit ? $flex_limit : $this->conf ['limit'];

		$this->config ['notify_mail'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'notify_mail', 's_form' );

		$this->config ['feedback_mail'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'feedback_mail', 's_form' );

		$this->config ['email_from_name'] = $this->conf ['email_from_name'];
		$this->config ['email_from_mail'] = $this->conf ['email_from_mail'];

		$this->config ['manual_backend_release'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'manual_backend_release', 's_form' );

		$this->config ['nolangfilter'] = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'nolangfilter', 's_list' );

		// Cutting the string in teaser mode
		$flex_teasercut = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'teasercut', 's_list' );
		$this->config ['teasercut'] = $flex_teasercut ? $flex_teasercut : $this->conf ['teasercut'];

		//for tt_news
		$this->tt_news = $this->getAddParams ( $this->tt_news_url_params_list );

		if ($this->tt_news ['tx_news_pi1[news]'] > 0) {
			$this->for_tt_news = true;
		}

		// Cutting words
		$flex_wordcut = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'wordcut', 's_list' );
		$this->config ['wordcut'] = $flex_wordcut ? $flex_wordcut : $this->conf ['wordcut'];

		// Sorting options for the list & teaser mode
		$flex_sortingField = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'listOrderBy', 's_list' );
		$flex_sortingDirection = $this->pi_getFFvalue ( $this->cObj->data ['pi_flexform'], 'ascDesc', 's_list' );

		$this->config ['sortingField'] = $flex_sortingField ? $flex_sortingField : $this->conf ['sortingField'];
		$this->config ['sortingDirection'] = $flex_sortingDirection ? $flex_sortingDirection : $this->conf ['sortingDirection'];

		if (t3lib_extMgm::isLoaded ( 'sr_freecap' ) and $this->config ['captcha'] == 'sr_freecap') {

			require_once (t3lib_extMgm::extPath ( 'sr_freecap' ) . 'pi2/class.tx_srfreecap_pi2.php');

			$this->freeCap = t3lib_div::makeInstance ( 'tx_srfreecap_pi2' );
		}
	}
	/**
	 * Main Function: Mcalls the init() function to setup the configuration and decides by the
	 * given CODEs which of the functions to display news should by called.
	 *
	 * @param	string		$content: function output is added to this
	 * @param	array		$conf: configuration array
	 * @return	string		$content: complete content generated by the plugin
	 */
	function main($content, $conf) {
		$this->local_cObj = t3lib_div::makeInstance ( 'tslib_cObj' ); // Local cObj.
		$this->init ( $conf );

		switch ($this->code) {
			case 'TEASER' :
			case 'LIST' :
				$content = $this->displayList ();
				break;
			case 'FORM' :
				$content .= $this->displayForm ();
				break;
			default :
				// To-Do: Help Template
				break;
		}

		return $this->pi_wrapInBaseClass ( $content );
	}

	/**
	 * Generating the list of guestbook entries for the modes LIST & TEASER
	 *
	 * @return	string		$content: generated content
	 */
	function displayList() {

		if (! $this->config ['nolangfilter']) {
			$language_filter = ' AND (sys_language_uid = ' . $this->sys_language_uid . ' OR sys_language_uid = -1) ';
		}

		$temp_where = 'pid IN (' . $this->config ['pid_list'] . ')' . $language_filter . $this->cObj->enableFields ( $this->strEntryTable );
		if ($this->for_tt_news) {
			$temp_where = ' uid_tt_news=' . intval ( $this->tt_news ['tx_news_pi1[news]'] ) . ' AND ' . $temp_where;
		}
		$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', $this->strEntryTable, $temp_where );

		$count = $GLOBALS ['TYPO3_DB']->sql_num_rows ( $res );

		$templateName = 'TEMPLATE_' . $this->code;

		// Teaser specific wrapping
		if ($this->code == 'TEASER') {
			$markerArray ['###TEASER_HEADLINE###'] = $this->pi_getLL ( 'teaser_headline' );

			if ($this->config ['guestbook'] > 0) {
				$markerArray ['###TEASER_MORE###'] = $this->pi_getLL ( 'teaser_more' );

				$wrappedSubpartArray = array ();
				$wrappedSubpartArray ['###LINK_ITEM###'] = explode ( '|', $this->pi_linkTP_keepPIvars ( '|', array (), 1, '', $this->config ['guestbook'] ) );
			} else {
				$markerArray ['###TEASER_MORE###'] = '';
				$wrappedSubpartArray = array ();
			}

		} else {
			$markerArray ['###TEASER_HEADLINE###'] = '';
			$markerArray ['###TEASER_MORE###'] = '';
		}

		$list = $this->cObj->getSubpart ( $this->templateCode, '###' . $templateName . '###' );

		if ($this->config ['limit'] > 0 && $count > $this->config ['limit'] && $this->code == 'LIST') {
			// configure pagebrowser
			$this->internal ['res_count'] = $count;

			$this->internal ['maxPages'] = $this->conf ['pageBrowser.'] ['maxPages'] > 0 ? $this->conf ['pageBrowser.'] ['maxPages'] : 10;

			$this->internal ['results_at_a_time'] = $this->config ['limit'];

			if (! $this->conf ['pageBrowser.'] ['showPBrowserText']) {
				$this->LOCAL_LANG [$this->LLkey] ['pi_list_browseresults_page'] = '';
			}

			$markerArray ['###BROWSE_LINKS###'] = $this->pi_list_browseresults ( $this->conf ['pageBrowser.'] ['showResultCount'], $this->conf ['pageBrowser.'] ['tableParams'] );

			$markerArray = $this->getPageBrowser ( $markerArray );

		} else {

			$this->internal ['res_count'] = $count;

			$this->internal ['maxPages'] = $this->conf ['pageBrowser.'] ['maxPages'] > 0 ? $this->conf ['pageBrowser.'] ['maxPages'] : 10;

			$markerArray = $this->getPageBrowser ( $markerArray );

			$markerArray ['###BROWSE_LINKS###'] = '';
			$markerArray ['###LINK_PREV###'] = '';
			$markerArray ['###PAGES###'] = '';
			$markerArray ['###LINK_NEXT###'] = '';
		}

		if ($count > 0) {

			if (! empty ( $this->config ['sortingField'] ) && ! empty ( $this->config ['sortingDirection'] )) {
				$orderBy = $this->config ['sortingField'] . ' ' . $this->config ['sortingDirection'];
			}

			if ($this->piVars ['pointer'] > 0) {
				$limit_start = $this->piVars ['pointer'] * $this->config ['limit'];
			} else {
				$limit_start = 0;
			}

			$temp_where = 'pid IN (' . $this->config ['pid_list'] . ')' . $language_filter . $this->cObj->enableFields ( $this->strEntryTable );
			if ($this->for_tt_news) {
				$temp_where = ' uid_tt_news=' . intval ( $this->tt_news ['tx_news_pi1[news]'] ) . ' AND ' . $temp_where;
			}

			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', $this->strEntryTable, $temp_where, '', $orderBy, $limit_start . ',' . $this->config ['limit'] );

			$entry = $GLOBALS ['TSFE']->cObj->getSubpart ( $list, '###ENTRY###' );
			$subpartArray ['###CONTENT###'] = $this->getListContent ( $res, $entry );

			// Adds hook for processing of extra item markers
			if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] )) {
				foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] as $_classRef ) {
					$_procObj = & t3lib_div::getUserObj ( $_classRef );
					$markerArray = $_procObj->extraItemMarkerProcessor ( $markerArray, array (), $this->config, $this );
				}
			}

			return $this->cObj->substituteMarkerArrayCached ( $list, $markerArray, $subpartArray, $wrappedSubpartArray );
		} else {
			$no_entries = $this->cObj->getSubpart ( $this->templateCode, '###TEMPLATE_NO_ENTRIES###' );

			$markerArray ['###NO_ENTRIES_HEADLINE###'] = $this->pi_getLL ( 'no_entries_headline' );
			$markerArray ['###NO_ENTRIES_TEXT###'] = $this->pi_getLL ( 'no_entries_text' );

			$no_entries = $this->cObj->substituteMarkerArrayCached ( $no_entries, $markerArray, array (), array () );

			return $no_entries;
		}
	}

	/**
	 * Getting the page browser for paging
	 *
	 * @param	array		$markerArray: Array containing the template marker
	 * @return	array		$markerArray: Array containing the template marker
	 */
	function getPageBrowser($markerArray) {
		$cache = 1;
		$newsCount = $this->internal ['res_count'];
		$begin_at = $this->piVars ['pointer'] * $this->config ['limit'];
		// Make Next link
		if ($newsCount > $begin_at + $this->config ['limit']) {
			$next = ($begin_at + $this->config ['limit'] > $newsCount) ? $newsCount - $this->config ['limit'] : $begin_at + $this->config ['limit'];
			$next = intval ( $next / $this->config ['limit'] );

			$params = ($next !== 0 ? array ($this->prefixId . '[pointer]' => $next ) : array ());

			if ($this->for_tt_news) {
				$params = array_merge ( $params, $this->tt_news );
			}

			$next_link = $this->pi_linkTP ( $this->pi_getLL ( 'pi_list_browseresults_next', 'Next >' ), $params, $cache );

			$markerArray ['###LINK_NEXT###'] = $this->local_cObj->stdWrap ( $next_link, $this->conf ['pageBrowser.'] ['next_stdWrap.'] );

		} else {
			$markerArray ['###LINK_NEXT###'] = '';
		}
		// Make Previous link
		if ($begin_at) {
			$prev = ($begin_at - $this->config ['limit'] < 0) ? 0 : $begin_at - $this->config ['limit'];
			$prev = intval ( $prev / $this->config ['limit'] );
			$params = ($prev !== 0 ? array ($this->prefixId . '[pointer]' => $prev ) : array ());

			if ($this->for_tt_news) {
				$params = array_merge ( $params, $this->tt_news );
			}

			$prev_link = $this->pi_linkTP ( $this->pi_getLL ( 'pi_list_browseresults_prev', '< Previous' ), $params, $cache );
			$markerArray ['###LINK_PREV###'] = $this->local_cObj->stdWrap ( $prev_link, $this->conf ['pageBrowser.'] ['previous_stdWrap.'] );
		} else {
			$markerArray ['###LINK_PREV###'] = '';
		}

		$firstPage = 0;
		$lastPage = $pages = ceil ( $newsCount / $this->config ['limit'] );
		$actualPage = floor ( $begin_at / $this->config ['limit'] );

		if (ceil ( $actualPage - $this->internal ['maxPages'] / 2 ) > 0) {
			$firstPage = ceil ( $actualPage - $this->internal ['maxPages'] / 2 );
			$addLast = 0;
		} else {
			$firstPage = 0;
			$addLast = floor ( ($this->internal ['maxPages'] / 2) - $actualPage );
		}

		if (ceil ( $actualPage + $this->internal ['maxPages'] / 2 ) <= $pages) {
			$lastPage = ceil ( $actualPage + $this->internal ['maxPages'] / 2 ) > 0 ? ceil ( $actualPage + $this->internal ['maxPages'] / 2 ) : 0;
			$subFirst = 0;
		} else {
			$lastPage = $pages;
			$subFirst = ceil ( $this->internal ['maxPages'] / 2 - ($pages - $actualPage) );
		}

		$firstPage = ($firstPage - $subFirst) > 0 ? ($firstPage - $subFirst) : $firstPage;
		$lastPage = ($lastPage + $addLast) <= $pages ? ($lastPage + $addLast) : $pages;

		for($i = $firstPage; $i < $lastPage; $i ++) {
			if (($begin_at >= $i * $this->config ['limit']) && ($begin_at < $i * $this->config ['limit'] + $this->config ['limit'])) {
				$item = ($this->conf ['pageBrowser.'] ['showPBrowserText'] ? $this->pi_getLL ( 'pi_list_browseresults_page', 'Page' ) : '') . ( string ) ($i + 1);
				$markerArray ['###PAGES###'] .= $this->local_cObj->stdWrap ( $item, $this->conf ['pageBrowser.'] ['activepage_stdWrap.'] ) . ' ';
			} else {
				$item = ($this->conf ['pageBrowser.'] ['showPBrowserText'] ? $this->pi_getLL ( 'pi_list_browseresults_page', 'Page' ) : '') . ( string ) ($i + 1);

				$params = ($i !== 0 ? array ($this->prefixId . '[pointer]' => $i ) : array ());

				if ($this->for_tt_news) {
					$params = array_merge ( $params, $this->tt_news );
				}

				$link = $this->pi_linkTP ( $this->local_cObj->stdWrap ( $item, $this->conf ['pageBrowser.'] ['pagelink_stdWrap.'] ), $params, $cache ) . ' ';

				$markerArray ['###PAGES###'] .= $this->local_cObj->stdWrap ( $link, $this->conf ['pageBrowser.'] ['page_stdWrap.'] );
			}
		}

		$end_at = ($begin_at + $this->config ['limit']);

		if ($this->conf ['pageBrowser.'] ['showResultCount']) {

			$markerArray ['###RESULT_COUNT###'] = ($this->internal ['res_count'] ? sprintf ( str_replace ( '###SPAN_BEGIN###', '<span' . $this->pi_classParam ( 'browsebox-strong' ) . '>', $this->pi_getLL ( 'pi_list_browseresults_displays', 'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>' ) ), $this->internal ['res_count'] > 0 ? ($begin_at + 1) : 0, min ( array ($this->internal ['res_count'], $end_at ) ), $this->internal ['res_count'] ) : $this->pi_getLL ( 'pi_list_browseresults_noResults', 'Sorry, no items were found.' ));
		} else {
			$markerArray ['###RESULT_COUNT###'] = '';
		}

		return $markerArray;
	}

	/**
	 * Generating the rows of guestbook entries
	 *
	 * @param	mixed		$res: database result of the current selection
	 * @param	string		$templatecode: HTML code including the template markers
	 * @return	string		$entries: Generated entries (HTML)
	 */
	function getListContent($res, $templatecode) {

		while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {

			if ($cc == $this->config ['limit']) {
				break;
			}

			$current_templatecode = $templatecode;

			$markerArray = $this->getItemMarkerArray ( $row );

			if (empty ( $row ['email'] )) {

				$current_templatecode = $this->cObj->substituteSubpart ( $current_templatecode, '###ENTRY_EMAIL###', '' );
			}

			if (empty ( $row ['homepage'] ) or $row ['homepage'] == 'http://') {

				$current_templatecode = $this->cObj->substituteSubpart ( $current_templatecode, '###ENTRY_HOMEPAGE###', '' );
			}

			if (empty ( $row ['entrycomment'] )) {

				$current_templatecode = $this->cObj->substituteSubpart ( $current_templatecode, '###ENTRY_ENTRYCOMMENT###', '' );
			}

			// Adds hook for processing of extra subparts
			if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraSubpartHook'] )) {
				foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraSubpartHook'] as $_classRef ) {
					$_procObj = & t3lib_div::getUserObj ( $_classRef );
					$current_templatecode = $_procObj->extraSubpartProcessor ( $row, $current_templatecode, $this );
				}
			}

			$entries .= $this->cObj->substituteMarkerArrayCached ( $current_templatecode, $markerArray, array (), array () );

			$cc ++;
		}

		return $entries;
	}

	/**
	 * Fills in the markerArray with data for a guestbook entry
	 *
	 * @param	array		$row: result row for a news item
	 * @return	array		$markerArray: filled marker array
	 */
	function getItemMarkerArray($row) {

		$markerArray = $this->initFormMarkerArray ();

		$markerArray ['###GUESTBOOK_UID###'] = $row ['uid'];
		$markerArray ['###GUESTBOOK_FIRSTNAME###'] = $this->cutDown ( $row ['firstname'] );
		$markerArray ['###GUESTBOOK_SURNAME###'] = $this->cutDown ( $row ['surname'] );

		if (! empty ( $row ['place'] )) {
			$markerArray ['###GUESTBOOK_FROM###'] = $this->pi_getLL ( 'list_from_place' );
			$markerArray ['###GUESTBOOK_PLACE###'] = $this->cutDown ( $row ['place'] );
		} else {
			$markerArray ['###GUESTBOOK_FROM###'] = '';
			$markerArray ['###GUESTBOOK_PLACE###'] = '';
		}

		if (! empty ( $row ['email'] )) {
			$markerArray ["###GUESTBOOK_EMAIL_URL###"] = htmlspecialchars ( $this->get_url ( '', $row ['email'] ) );

			if (! empty ( $this->config ['email_subst'] )) {
				$markerArray ["###GUESTBOOK_EMAIL###"] = str_replace ( '@', $this->config ['email_subst'], trim ( $row ['email'] ) );
			} else {
				$markerArray ["###GUESTBOOK_EMAIL###"] = trim ( $row ['email'] );
			}
		}

		if (! empty ( $row ['homepage'] ) && $row ['homepage'] != 'http://') {
			$markerArray ["###GUESTBOOK_HOMEPAGE_URL###"] = htmlspecialchars ( $this->get_url ( '', $row ['homepage'] ) );
			$markerArray ["###GUESTBOOK_HOMEPAGE###"] = $this->cObj->typolink ( $this->cObj->stdWrap ( $row ['homepage'], $this->conf ['homepage.'] ), $conf );
		}

		$markerArray ['###GUESTBOOK_DATE###'] = $this->local_cObj->stdWrap ( $row ['crdate'], $this->conf ['datetime_stdWrap.'] );
		$markerArray ['###GUESTBOOK_ONLYTIME###'] = $this->local_cObj->stdWrap ( $row ['crdate'], $this->conf ['time_stdWrap.'] );
		$markerArray ['###GUESTBOOK_ONLYDATE###'] = $this->local_cObj->stdWrap ( $row ['crdate'], $this->conf ['date_stdWrap.'] );

		$row ['entry'] = $this->cutDown ( stripslashes ( $row ['entry'] ) );
		$row ['entrycomment'] = stripslashes ( $row ['entrycomment'] );

		if ($this->code == 'TEASER') {
			$words = split ( ' ', $row ['entry'] );
			if (is_array ( $words ) && count ( $words ) > 0 && strlen ( $row ['entry'] ) > $this->config ['teasercut']) {
				foreach ( $words as $word ) {
					if (strlen ( $teaser ) <= $this->config ['teasercut']) {
						$teaser .= $word . ' ';
					}
				}
				$markerArray ['###GUESTBOOK_ENTRY###'] = $teaser . '[...]';
			} else {
				$markerArray ['###GUESTBOOK_ENTRY###'] = $row ['entry'];
			}

		} else {
			$markerArray ['###GUESTBOOK_ENTRY###'] = nl2br ( $row ['entry'] );
		}

		$markerArray ['###GUESTBOOK_ENTRY###'] = $this->substituteEmoticons ( $markerArray ['###GUESTBOOK_ENTRY###'] );
		$markerArray ['###GUESTBOOK_ENTRYCOMMENT###'] = $this->substituteEmoticons ( nl2br ( $row ['entrycomment'] ) );

		// Adds hook for processing of extra item markers
		if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] )) {
			foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] as $_classRef ) {
				$_procObj = & t3lib_div::getUserObj ( $_classRef );
				$markerArray = $_procObj->extraItemMarkerProcessor ( $markerArray, $row, $this->config, $this );
			}
		}

		return $markerArray;
	}

	/**
	 * Cutting down a string
	 *
	 * @param	string	$text: String that has to be cut down
	 * @return	string	Shorten string
	 */
	function cutDown($text) {

		if ($this->config ['wordcut'] < 1)
			return $text;

		if (strlen ( $text ) <= $this->config ['wordcut'])
			return $text;

		$splitted_words = explode ( ' ', $text );

		if (is_array ( $splitted_words ) && count ( $splitted_words ) > 0) {

			$cutwords = array ();

			foreach ( $splitted_words as $word ) {
				if (strlen ( $word ) > $this->config ['wordcut'])
					$cutwords [] = substr ( $word, 0, $this->config ['wordcut'] ) . '[..]';
				else
					$cutwords [] = $word;
			}

			return implode ( ' ', $cutwords );

		} else
			return $text;
	}

	/**
	 * Generates a pibase-compliant typolink
	 *
	 * @param	string		$tag: string to include within <a>-tags; if empty, only the url is returned
	 * @param	string		$id: page id (could of the form id,type )
	 * @param	array		$vars: extension variables to add to the url ($key, $value)
	 * @param	array		$unsetVars: extension variables (piVars to unset)
	 * @param	boolean		$usePiVars: if set, input vars and incoming piVars arrays are merge
	 * @return	string		generated link or url
	 */
	function get_url($tag = '', $id, $vars = array(), $unsetVars = array(), $usePiVars = true) {

		$vars = ( array ) $vars;
		$unsetVars = ( array ) $unsetVars;
		if ($usePiVars) {
			$vars = array_merge ( $this->piVars, $vars ); //vars override pivars
			while ( list ( , $key ) = each ( $unsetVars ) ) {
				// unsetvars override anything
				unset ( $vars [$key] );
			}
		}
		while ( list ( $key, $val ) = each ( $vars ) ) {
			$piVars [$this->prefixId . '[' . $key . ']'] = $val;
		}
		if ($tag) {
			return $this->cObj->getTypoLink ( $tag, $id, $piVars );

		} else {
			return $this->cObj->getTypoLink_URL ( $id, $piVars );
		}
	}

	/**
	 * Getting marker array for formular elements
	 *
	 * @return array
	 */
	function initFormMarkerArray() {
		$markerArray = array ();

		if (is_array ( $this->LOCAL_LANG [$this->LLkey] )) {

			foreach ( $this->LOCAL_LANG [$this->LLkey] as $key => $value ) {
				if (strpos ( $key, 'form_' ) === 0) {
					$markerArray ['###' . strtoupper ( $key ) . '###'] = $value[0]['target'];
				}
			}
		}
		return $markerArray;
	}

	/**
	 * Generating the form for new guestbook entries
	 *
	 * @return	string		$content : the form (HTML)
	 */
	function displayForm() {
		if (is_array ( $this->LOCAL_LANG ) && count ( $this->LOCAL_LANG ) > 0) {
			$markerArray = $this->initFormMarkerArray ();

			$markerArray ['###PID###'] = $GLOBALS ["TSFE"]->id;
			$url = $this->getUrl ( $GLOBALS ["TSFE"]->id );
			$markerArray ['###ACTION_URL###'] = htmlspecialchars ( $url );

			$markerArray ['###FORM_ERROR###'] = '';
			$markerArray ['###FORM_ERROR_FIELDS###'] = '';

			// <Frank Nägler added onErrorHook>
			if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['onErrorHook'] )) {
				foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['onErrorHook'] as $_classRef ) {
					$_procObj = & t3lib_div::getUserObj ( $_classRef );
					$_procObj->onErrorProcessor ( $error, $this );
				}
			}
			// </Frank Nägler added onErrorHook>


			$this->postvars = t3lib_div::_GP ( 'tx_veguestbook_pi1' ) ?  t3lib_div::_GP( 'tx_veguestbook_pi1' ) : array ();
			if (isset ( $this->postvars ['submitted'] ) && $this->postvars ['submitted'] == 1) {

				foreach ( $this->postvars as $key => $value ) {
					$value = $this->local_cObj->removeBadHTML ( $value, array () );
					$this->postvars [$key] = $value;
				}

				if (isset ( $this->postvars ['homepage'] )) {
					if (! strstr ( $this->postvars ['homepage'], 'http://' ) && ! empty ( $this->postvars ['homepage'] )) {
						$this->postvars ['homepage'] = 'http://' . $this->postvars ['homepage'];
					}
				}

				foreach ( $this->postvars as $k => $v ) {
					$markerArray ['###VALUE_' . strtoupper ( $k ) . '###'] = stripslashes ( $v );
				}

				$error = $this->checkForm ();

				if (! empty ( $error )) {
					$markerArray ['###FORM_ERROR###'] = $this->pi_getLL ( 'form_error' );
					$markerArray ['###FORM_ERROR_FIELDS###'] = $error;
				} else {

					$db_fields = array ('firstname', 'surname', 'email', 'homepage', 'place', 'entry', 'entrycomment');

					$saveData ['uid'] = '';
					$saveData ['pid'] = $this->config ['pid_list'];
					$saveData ['tstamp'] = time ();
					$saveData ['crdate'] = time ();
					$saveData ['deleted'] = '0';
					$saveData ['sys_language_uid'] = $this->sys_language_uid;
					$saveData ['remote_addr'] = $_SERVER ['REMOTE_ADDR'];

					if ($this->for_tt_news) {
						$saveData ['uid_tt_news'] = $this->tt_news ['tx_news_pi1[news]'];
					}

					if ($this->config ['manual_backend_release'] == 1) {
						$saveData ['hidden'] = '1';
					} else {
						$saveData ['hidden'] = '0';
					}

					foreach ( $this->postvars as $k => $v ) {
						if (in_array ( $k, $db_fields )) {

							if ($this->config ['allowedTags']) {
								$v = strip_tags ( $v, $this->config ['allowedTags'] );
							}

							$saveData [$k] = $this->local_cObj->removeBadHTML ( $v, array () );
						}
					}

					if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['preEntryInsertHook'] )) {
						foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['preEntryInsertHook'] as $_classRef ) {
							$_procObj = & t3lib_div::getUserObj ( $_classRef );
							$saveData = $_procObj->preEntryInsertProcessor ( $saveData, $this );
						}
					}

					$insert = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( $this->strEntryTable, $saveData );

					if ($insert) {

						if (! empty ( $this->config ['notify_mail'] )) {
							$this->sendNotificationMail ( $this->config ['notify_mail'] );
						}

						if (! empty ( $this->postvars ['email'] ) && $this->config ['feedback_mail']) {
							$this->sendFeedbackMail ( $this->postvars ['email'] );
						}

						if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['postEntryInsertedHook'] )) {
							foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['postEntryInsertedHook'] as $_classRef ) {
								$_procObj = & t3lib_div::getUserObj ( $_classRef );
								$_procObj->postEntryInsertedProcessor ( $this );
							}
						}

						// clear cache
						$TCE = t3lib_div::makeInstance ( 't3lib_TCEmain' );
						$TCE->admin = 1;

						// Clear cache for pages entered in TSconfig:
						if ($this->config ['clearCacheCmdOnInsert']) {
							$commands = t3lib_div::trimExplode ( ',', strtolower ( $this->config ['clearCacheCmdOnInsert'] ), 1 );
							$commands = array_unique ( $commands );
							foreach ( $commands as $commandPart ) {
								$GLOBALS ['TSFE']->clearPageCacheContent_pidList ( $commandPart );
							}
						}

						$GLOBALS ['TSFE']->clearPageCacheContent_pidList ( $GLOBALS ['TSFE']->id );
						$GLOBALS ['TSFE']->clearPageCacheContent_pidList ( $this->config ['redirect_page'] );

						// clear index
						if (t3lib_extMgm::isLoaded ( 'indexed_search' )) {

							$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'phash', 'index_phash', ' data_page_id = ' . $this->config ['redirect_page'] );

							if ($res) {
								while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $res ) ) {
									$phash = ( int ) $row ['phash'];
									if ($phash > 0) {
										tx_indexedsearch_indexer::removeOldIndexedPages ( $phash );
									}
								}
							}
						}

						header ( 'Location: ' . $this->getUrl ( $this->config ['redirect_page'] ) );
					}
				}
			}

			// Pre-fill form data if FE user in logged in
			if (! $this->postvars && $GLOBALS ['TSFE']->loginUser) {
				$surname_pos = strpos ( $GLOBALS ['TSFE']->fe_user->user ['name'], ' ' );
				$markerArray ['###VALUE_FIRSTNAME###'] = substr ( $GLOBALS ['TSFE']->fe_user->user ['name'], 0, $surname_pos );
				$markerArray ['###VALUE_SURNAME###'] = substr ( $GLOBALS ['TSFE']->fe_user->user ['name'], ($surname_pos + 1) );
				$markerArray ['###VALUE_EMAIL###'] = $GLOBALS ['TSFE']->fe_user->user ['email'];
				$markerArray ['###VALUE_HOMEPAGE###'] = $GLOBALS ['TSFE']->fe_user->user ['www'];
				$markerArray ['###VALUE_PLACE###'] = $GLOBALS ['TSFE']->fe_user->user ['city'];
			}

			$markerArray = $this->markObligationFields ( $markerArray );

			$this->status = 'displayForm';

			// Adds hook for processing of extra item markers
			if (is_array ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] )) {
				foreach ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['ve_guestbook'] ['extraItemMarkerHook'] as $_classRef ) {
					$_procObj = & t3lib_div::getUserObj ( $_classRef );
					$markerArray = $_procObj->extraItemMarkerProcessor ( $markerArray, $row, $this->config, $this );
				}
			}

			$template = $this->cObj->getSubpart ( $this->templateCode, '###TEMPLATE_FORM###' );

			if (! $GLOBALS ['TSFE']->loginUser) {
				if (is_object ( $this->freeCap ) and $this->config ['captcha'] == 'sr_freecap') {
					$markerArray = array_merge ( $markerArray, $this->freeCap->makeCaptcha () );
					$template = $this->cObj->substituteSubpart ( $template, '###CAPTCHA_INSERT###', '' );
				} elseif (t3lib_extMgm::isLoaded ( 'captcha' ) and $this->config ['captcha'] == 'captcha') {
					$markerArray ['###CAPTCHA_IMAGE###'] = '<img src="' . t3lib_extMgm::siteRelPath ( 'captcha' ) . 'captcha/captcha.php" alt="" />';
					$template = $this->cObj->substituteSubpart ( $template, '###SR_FREECAP_INSERT###', '' );
				} else {
					$template = $this->cObj->substituteSubpart ( $template, '###SR_FREECAP_INSERT###', '' );
					$template = $this->cObj->substituteSubpart ( $template, '###CAPTCHA_INSERT###', '' );
				}
			} else {
				$template = $this->cObj->substituteSubpart ( $template, '###SR_FREECAP_INSERT###', '' );
				$template = $this->cObj->substituteSubpart ( $template, '###CAPTCHA_INSERT###', '' );
			}

			$form = $this->cObj->substituteMarkerArrayCached ( $template, $markerArray, array (), array () );

			$form = preg_replace ( '/###[A-Za-z_1234567890]+###/', '', $form );

			return $form;
		}
	}

	/**
	 * Returns an array with additional Link parameters
	 * from chc-forum @author Zach Davis <zach@crito.org>
	 *
	 * @param	string		$addParamsList: comma-seperated list of parameters that will be added to all links.
	 * @return	array		additional link parameters in an array
	 */
	function getAddParams($addParamsList) {
		$queryString = explode ( '&', t3lib_div::implodeArrayForUrl ( '', $GLOBALS ['_GET'] ) );

		if ($queryString) {
			while ( list ( , $val ) = each ( $queryString ) ) {
				$tmp = explode ( '=', $val );
				$paramArray [$tmp [0]] = $tmp [1];
			}

			while ( list ( $pk, $pv ) = each ( $paramArray ) ) {

				if (t3lib_div::inList ( $addParamsList, $pk )) {
					$addParamArray [$pk] = $pv;
				}
			}
		}

		return $addParamArray;
	}

	/**
	 * Gets the e-mail name out of the configuration
	 *
	 * @return	string		Name
	 */
	function getEmailFromName() {
		if (! empty ( $this->config ['email_from_name'] ))
			return $this->config ['email_from_name'];
		else
			return "ve_guestbook";
	}

	/**
	 * Gets the e-mail address out of the configuration
	 *
	 * @return	string		E-Mail
	 */
	function getEmailFromMail() {
		if (! empty ( $this->config ['email_from_mail'] ))
			return $this->config ['email_from_mail'];
		else
			return "ve_guestbook@" . $_SERVER ['SERVER_NAME'];
		;
	}

	/**
	 * After submitting a new entry you can activate a notification mail to remind the admin
	 *
	 * @param	string		$emailto: E-Mail recipient
	 * @return	boolean		Mail delivery: true / false
	 */
	function sendNotificationMail($emailto) {
		$notification_mail_subject = $this->pi_getLL ( 'notification_mail_subject' );
		$notification_mail_text = $this->pi_getLL ( 'notification_mail_text' );

		$markerArray ['###SERVER_NAME###'] = $_SERVER ['SERVER_NAME'];
		$markerArray ['###URL###'] = t3lib_div::getIndpEnv ( 'TYPO3_SITE_URL' ) . $this->getUrl ( $this->config ['guestbook'] );

		if (is_array ( $this->postvars )) {
			foreach ( $this->postvars as $k => $v ) {
				$markerArray ['###' . strtoupper ( $k ) . '###'] = stripslashes ( $v );
			}

			$notification_mail_subject = $this->cObj->substituteMarkerArrayCached ( $notification_mail_subject, $markerArray, array (), array () );
			$notification_mail_text = $this->cObj->substituteMarkerArrayCached ( $notification_mail_text, $markerArray, array (), array () );

			$emailfrom_name = $this->getEmailFromName ();
			$emailfrom = $this->getEmailFromMail ();

			return t3lib_div::plainMailEncoded ( $emailto, $notification_mail_subject, $notification_mail_text, "From: " . $emailfrom_name . " <" . $emailfrom . ">\r\nReply-To: " . $emailfrom );
		}
	}

	/**
	 * After submitting a new entry you can activate a feedback mail for the submitting user
	 *
	 * @param	string		$emailto: E-Mail recipient
	 * @return	boolean		Mail delivery: true / false
	 */
	function sendFeedbackMail($emailto) {

		$notification_mail_subject = $this->pi_getLL ( 'feedback_mail_subject' );
		$notification_mail_text = $this->pi_getLL ( 'feedback_mail_text' );

		$markerArray ['###SERVER_NAME###'] = $_SERVER ['SERVER_NAME'];
		$markerArray ['###URL###'] = t3lib_div::getIndpEnv ( 'TYPO3_SITE_URL' ) . $this->getUrl ( $this->config ['guestbook'] );

		if (is_array ( $this->postvars )) {
			foreach ( $this->postvars as $k => $v ) {
				$markerArray ['###' . strtoupper ( $k ) . '###'] = stripslashes ( $v );
			}

			$notification_mail_subject = $this->cObj->substituteMarkerArrayCached ( $notification_mail_subject, $markerArray, array (), array () );
			$notification_mail_text = $this->cObj->substituteMarkerArrayCached ( $notification_mail_text, $markerArray, array (), array () );

			$emailfrom_name = $this->getEmailFromName ();
			$emailfrom = $this->getEmailFromMail ();

			return t3lib_div::plainMailEncoded ( $emailto, $notification_mail_subject, $notification_mail_text, "From: " . $emailfrom_name . " <" . $emailfrom . ">\r\nReply-To: " . $emailfrom );
		}
	}
	/**
	 * Getting the URL to the given ID with all needed params
	 *
	 * @param	integer		$id: Page ID
	 * @return	string		$url: URL
	 */
	function getUrl($id) {
		if (t3lib_div::_GP ( 'L' )) {
			$urlParameters ['L'] = t3lib_div::_GP ( 'L' );
		}

		if (is_array ( $urlParameters )) {
			if (is_array ( $this->tt_news )) {
				$urlParameters = array_merge ( $urlParameters, $this->tt_news );
			}
		} else {
			$urlParameters = $this->tt_news;
		}

		return $this->pi_getPageLink ( $id, '', $urlParameters );
	}

	/**
	 * All obligation fields will be marked in the form
	 *
	 * @param	array		$markerArray: template marker array
	 * @return	array		$markerArray: filled template marker array
	 */
	function markObligationFields($markerArray) {
		if (is_array ( $this->config ['obligationfields'] ) && count ( $this->config ['obligationfields'] ) > 0) {
			foreach ( $this->config ['obligationfields'] as $obl_field ) {
				$markerArray ['###FORM_' . strtoupper ( $obl_field ) . '_OBLIGATION###'] = '*';
			}
		}

		return $markerArray;
	}

	/**
	 * Emoticons substitution
	 * wrap the string with this function to replace emoticontags with emoticonicons
	 *
	 * @param	string		the message
	 * @return	string		The message with replaced emoticon string as image
	 */
	function substituteEmoticons($str) {
		if ($this->conf ['emoticons.'] ['active'] == 1) {
			reset ( $this->conf ['emoticons.'] ['subst.'] );
			$emoConf = $this->conf ['emoticons.'];
			while ( list ( $source, $dest ) = each ( $emoConf ['subst.'] ) ) {
				$imgFile = str_replace ( '###EMOTICON###', $dest ['val'], $emoConf ['10.'] ['file'] );

				if (strstr ( $dest ['str'], '||' )) {
					$aohIcons = explode ( '||', $dest ['str'] );

					foreach ( $aohIcons as $icon ) {

						$str = str_replace ( trim ( $icon ), $this->cObj->cImage ( $imgFile, $emoConf ['10.'] ), $str );
					}
				} else {
					$str = str_replace ( trim ( $dest ['str'] ), $this->cObj->cImage ( $imgFile, $emoConf ['10.'] ), $str );
				}
			}
		}
		return $str;
	}

	/**
	 * Method for checking the user input in the form mode
	 *
	 * @return	string		$error Error-Code (HTML)
	 */
	function checkForm() {

		if (is_array ( $this->config ['obligationfields'] ) && count ( $this->config ['obligationfields'] ) > 0) {
			foreach ( $this->config ['obligationfields'] as $obl_field ) {
				if (empty ( $this->postvars [$obl_field] )) {
					$error .= '<li>' . ucfirst ( $this->pi_getLL ( 'form_' . $obl_field ) ) . "</li>\n";
					$this->errorFields ['obligationfields'] = $obl_field;
				}
			}
		}

		if ($this->config ['email_validation'] && ! empty ( $this->postvars ['email'] )) {

			if (t3lib_div::validEmail ( $this->postvars ['email'] ) == false) {
				$error .= '<li>' . $this->pi_getLL ( 'form_email' ) . " (" . $this->pi_getLL ( 'form_invalid_field' ) . ")</li>\n";
				$this->errorFields ['email_validation'] = false;
			}
		}

		if ($this->config ['website_validation'] && ! empty ( $this->postvars ['homepage'] ) && $this->postvars ['homepage'] != 'http://') {
			if ($this->isURL ( $this->postvars ['homepage'] ) == false) {
				$error .= '<li>' . ucfirst ( $this->pi_getLL ( 'form_homepage' ) ) . " (" . $this->pi_getLL ( 'form_invalid_field' ) . ")</li>\n";
				$this->errorFields ['website_validation'] = false;
			}
		}

		// blacklist validation
		if ($this->config ['blacklist_mail'] && ! empty ( $this->postvars ['email'] )) {
			$emails_blacklisted = split ( ",", $this->config ['blacklist_mail'] );
			if (is_array ( $emails_blacklisted )) {
				foreach ( $emails_blacklisted as $single_email ) {

					if (! (strpos ( $this->postvars ['email'], trim ( $single_email ) ) === false)) {
						$errorBlacklist = '<li>' . $this->pi_getLL ( 'form_email' ) . " (" . $this->pi_getLL ( 'form_blacklisted' ) . ")</li>\n";
						$this->errorFields ['blacklist_mail'] = false;
						break;
					}
				}
			}
		}

		// whitelist validation
		if ($this->config ['whitelist_mail'] && ! empty ( $this->postvars ['email'] )) {
			$emails_whitelisted = split ( ",", $this->config ['whitelist_mail'] );
			if (is_array ( $emails_whitelisted )) {
				foreach ( $emails_whitelisted as $single_email ) {
					if (! (strpos ( $this->postvars ['email'], trim ( $single_email ) ) === false)) {
						$errorBlacklist = '';
						$this->errorFields ['blacklist_mail'] = true;
						break;
					}
				}
			}
		}

		if (! $GLOBALS ['TSFE']->loginUser) {
			if (is_object ( $this->freeCap ) && $this->config ['captcha'] == 'sr_freecap' && ! $this->freeCap->checkWord ( $this->postvars ['captcha_response'] )) {
				$error .= '<li>' . ucfirst ( $this->pi_getLL ( 'form_captcha_response' ) ) . " (" . $this->pi_getLL ( 'form_invalid_field' ) . ")</li>\n";
				$this->errorFields ['captcha'] = false;
			}
		}

		if (t3lib_extMgm::isLoaded ( 'captcha' ) && $this->config ['captcha'] == 'captcha') {

			session_start ();
			if (isset ( $_SESSION ['tx_captcha_string'] )) {
				$captchaStr = $_SESSION ['tx_captcha_string'];
				$_SESSION ['tx_captcha_string'] = '';

				if ($captchaStr != $this->postvars ['captcha_response'] && ! empty ( $captchaStr )) {
					$error .= '<li>' . ucfirst ( $this->pi_getLL ( 'form_captcha_response' ) ) . " (" . $this->pi_getLL ( 'form_invalid_field' ) . ")</li>\n";
					$this->errorFields ['captcha'] = false;
				}
			} else {
				$error .= '<li>' . ucfirst ( $this->pi_getLL ( 'form_error_cookie' ) ) . '</li>';
				$this->errorFields ['captcha'] = false;
			}
		}

		$error .= $errorBlacklist;

		if (! empty ( $error )) {
			return "<ul>\n" . $error . "</ul>\n";
		}
	}

	/**
	 * URL validation method
	 *
	 * @param	string		$url: URL to validate
	 * @return	boolean		Success: valid / not valid
	 */
	function isURL($url) {
		if (! preg_match ( '#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $url )) {
			return false;
		} else {
			return true;
		}
	}
}

if (defined ( "TYPO3_MODE" ) && $TYPO3_CONF_VARS [TYPO3_MODE] ["XCLASS"] ["ext/ve_guestbook/pi1/class.tx_veguestbook_pi1.php"]) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ["XCLASS"] ["ext/ve_guestbook/pi1/class.tx_veguestbook_pi1.php"]);
}

?>
