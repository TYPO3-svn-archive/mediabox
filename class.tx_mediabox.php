<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004 Kasper Skaarhoj (kasper@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
require_once(PATH_tslib.'class.tslib_content.php');

/**
 * Class for handling mediabox-links
 *
 * @author	Markus Martens <typo3@jobesoft.de>
 * @package TYPO3
 * @subpackage tx_mediabox
 */
class tx_mediabox {

	/**
	 * Parse mediabox-links
	 * This method gets called by the typoLink_PostProc hook in tslib_content:
	 *
	 * @param   array   $parameters: Array of parameters from typoLink_PostProc hook in tslib_content
	 * @param   object  $cObj: Reference to the calling tslib_content instance
	 * @return	void
	 */
	public function typoLink_PostProc(&$parameters, &$pObj){
		if(preg_match('/ target="@(\w+)"/i',$parameters['finalTagParts']['targetParams'],$parts)){
      print(sprintf("<!-- [%s] %s:%s#%d -->\n",date('d.m.Y H:i:s'),__FILE__,__FUNCTION__,__LINE__));//DEBUG
      $aTag = array();
      $aTagParams = new SimpleXMLElement($parameters['finalTag'].'</a>');
      foreach( $aTagParams->attributes() as $key => $value )if(strcasecmp($key,'target')){
        $aTag[] = $key.'="'.$value.'"';
      }else{
        $aTag[] = sprintf('rel="lightbox[%s]"',$parts[1]);
      }
      $parameters['finalTag'] = '<a '.implode(' ',$aTag).'>';
		}
	}
  
  public function scanPage($content,$conf){
    print(sprintf("<!-- [%s] %s:%s#%d -->\n",date('d.m.Y H:i:s'),__FILE__,__FUNCTION__,__LINE__));//DEBUG
    unset($conf['userFunc']);
    $js  = "Mediabox.scanPage = function() {\n";
    $js .= "  var links = $$(\"a\").filter(function(el) {\n";
    $js .= "    return el.rel && el.rel.test(/^lightbox/i);\n";
    $js .= "  });\n";
    $js .= "  $$(links).mediabox(".self::array_js($conf,'  ').", null, function(el) {\n";
    $js .= "    var rel0 = this.rel.replace(/[[]|]/gi,\" \");\n";
    $js .= "    var relsize = rel0.split(\" \");\n";
    $js .= "    return (this == el) || ((this.rel.length > 8) && el.rel.match(relsize[1]));\n";
    $js .= "  });\n";
    $js .= "};\n";
    $js .= "window.addEvent(\"domready\", Mediabox.scanPage);\n";
    return "<script type=\"text/javascript\">\n".$js."</script>\n";
  }
  
  // converts an php-array into an js-array
  private static function array_js($conf,$prefix=''){
    $line = array();
    if(is_numeric(implode('',array_keys($conf)))){// numeric array
      foreach($conf as $key => $value){
        if(is_array($value))
          $line[] = self::array_js($value,$prefix);
        elseif(is_numeric($value))
          $line[] = floatval($value);
        elseif(!strcasecmp($value,'true'))
          $line[] = 'true';
        elseif(!strcasecmp($value,'false'))
          $line[] = 'false';
        else
          $line[] = sprintf( "'%s'", $value );
      }
      return "[".implode(",",$line)."]";
    }else{// assoziative array
      foreach($conf as $key => $value){
        if(is_array($value))
          $line[] = sprintf( $prefix."  %s:%s", rtrim($key,'.'), self::array_js($value,$prefix.'  ') );
        elseif(is_numeric($value))
          $line[] = sprintf( $prefix."  %s:%s", $key, floatval($value) );
        elseif(!strcasecmp($value,'true'))
          $line[] = sprintf( $prefix."  %s:true", $key );
        elseif(!strcasecmp($value,'false'))
          $line[] = sprintf( $prefix."  %s:false", $key );
        else
          $line[] = sprintf( $prefix."  %s:'%s'", $key, $value );
      }
      return "{\n".implode(",\n",$line)."\n".$prefix."}";
    }
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mediabox/class.tx_mediabox.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mediabox/class.tx_mediabox.php']);
}

?>
