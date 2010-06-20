<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/* register hook */
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['typoLink_PostProc']['tx_mediabox'] = 'EXT:mediabox/class.tx_mediabox.php:&tx_mediabox->typoLink_PostProc';

?>