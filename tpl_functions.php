<?php
/**
 * DokuWiki Template Arctic Functions.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Klier <chi@chimeric.de>
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_LF')) define('DOKU_LF',"\n");

// load sidebar contents
$sbl   = explode(',',tpl_getConf('left_sidebar_content'));
$sbr   = explode(',',tpl_getConf('right_sidebar_content'));
$sbpos = tpl_getConf('sidebar');

// set notoc option and toolbar regarding the sitebar setup
switch($sbpos) {
  case 'both':
    $notoc = (in_array('toc',$sbl) || in_array('toc',$sbr)) ? true : false;
    $toolb = (in_array('toolbox',$sbl) || in_array('toolbox',$sbr)) ? true : false;
    break;
  case 'left':
    $notoc = (in_array('toc',$sbl)) ? true : false;
    $toolb = (in_array('toolbox',$sbl)) ? true : false;
    break;
  case 'right':
    $notoc = (in_array('toc',$sbr)) ? true : false;
    $toolb = (in_array('toolbox',$sbr)) ? true : false;
    break;
  case 'none':
    $notoc = false;
    $toolb = false;
    break;
}

/**
 * Prints the sidebars
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function arctic_tpl_sidebar($pos) {

    $sb_order   = ($pos == 'left') ? explode(',', tpl_getConf('left_sidebar_order'))   : explode(',', tpl_getConf('right_sidebar_order'));
    $sb_content = ($pos == 'left') ? explode(',', tpl_getConf('left_sidebar_content')) : explode(',', tpl_getConf('right_sidebar_content'));

    // process contents by given order
    foreach($sb_order as $sb) {
        if(in_array($sb,$sb_content)) {
            $key = array_search($sb,$sb_content);
            unset($sb_content[$key]);
            arctic_tpl_sidebar_dispatch($sb,$pos);
        }
    }

    // check for left content not specified by order
    if(is_array($sb_content) && !empty($sb_content) > 0) {
        foreach($sb_content as $sb) {
            arctic_tpl_sidebar_dispatch($sb,$pos);
        }
    }
}

/**
 * Dispatches the given sidebar type to return the right content
 *
 * @author Michael Klier <chi@chimeric.de>
 * @author Mark C. Prins <mprins@users.sf.net>
 */
function arctic_tpl_sidebar_dispatch($sb,$pos) {
    global $lang;
    global $conf;
    global $ID;
    global $REV;
    global $INFO;
    global $TOC;

    $svID  = $ID;   // save current ID
    $svREV = $REV;  // save current REV
    $svTOC = $TOC;  // save current TOC

    $pname = tpl_getConf('pagename');

    switch($sb) {

        case 'main':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            $main_sb = $pname;
            if(@page_exists($main_sb) && auth_quickaclcheck($main_sb) >= AUTH_READ) {
                $always = tpl_getConf('main_sidebar_always');
                if($always or (!$always && !getNS($ID))) {
                    print '<aside class="main_sidebar sidebar_box">' . DOKU_LF;
                    print p_sidebar_xhtml($main_sb,$pos) . DOKU_LF;
                    print '</aside>' . DOKU_LF;
                }
            } elseif(!@page_exists($main_sb) && auth_quickaclcheck($main_sb) >= AUTH_CREATE) {
                if(@file_exists(DOKU_TPLINC.'lang/'. $conf['lang'].'/nosidebar.txt')) {
                    $out = p_render('xhtml', p_get_instructions(io_readFile(DOKU_TPLINC.'lang/'.$conf['lang'].'/nosidebar.txt')), $info);
                } else {
                    $out = p_render('xhtml', p_get_instructions(io_readFile(DOKU_TPLINC.'lang/en/nosidebar.txt')), $info);
                }
                $link = '<a href="' . wl($pname) . '" class="wikilink2">' . $pname . '</a>' . DOKU_LF;
                print '<aside class="main_sidebar sidebar_box">' . DOKU_LF;
                print str_replace('LINK', $link, $out);
                print '</aside>' . DOKU_LF;
            }
            break;

        case 'namespace':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            $user_ns  = tpl_getConf('user_sidebar_namespace');
            $group_ns = tpl_getConf('group_sidebar_namespace');
            if(!preg_match("/^".$user_ns.":.*?$|^".$group_ns.":.*?$/", $svID)) { // skip group/user sidebars and current ID
                $ns_sb = _getNsSb($svID);
                if($ns_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
                    print '<aside class="namespace_sidebar sidebar_box">' . DOKU_LF;
                    print p_sidebar_xhtml($ns_sb,$pos) . DOKU_LF;
                    print '</aside>' . DOKU_LF;
                }
            }
            break;

        case 'user':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            $user_ns = tpl_getConf('user_sidebar_namespace');
            if(isset($INFO['userinfo']['name'])) {
                $user = $_SERVER['REMOTE_USER'];
                $user_sb = $user_ns . ':' . $user . ':' . $pname;
                if(@page_exists($user_sb)) {
                    $subst = array('pattern' => array('/@USER@/'), 'replace' => array($user));
                    print '<aside class="user_sidebar sidebar_box">' . DOKU_LF;
                    print p_sidebar_xhtml($user_sb,$pos,$subst) . DOKU_LF;
                    print '</aside>';
                }
                // check for namespace sidebars in user namespace too
                if(preg_match('/'.$user_ns.':'.$user.':.*/', $svID)) {
                    $ns_sb = _getNsSb($svID);
                    if($ns_sb && $ns_sb != $user_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
                        print '<aside class="namespace_sidebar sidebar_box">' . DOKU_LF;
                        print p_sidebar_xhtml($ns_sb,$pos) . DOKU_LF;
                        print '</aside>' . DOKU_LF;
                    }
                }

            }
            break;

        case 'group':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            $group_ns = tpl_getConf('group_sidebar_namespace');
            if(isset($INFO['userinfo']['name'], $INFO['userinfo']['grps'])) {
                foreach($INFO['userinfo']['grps'] as $grp) {
                    $group_sb = $group_ns.':'.$grp.':'.$pname;
                    if(@page_exists($group_sb) && auth_quickaclcheck(cleanID($group_sb)) >= AUTH_READ) {
                        $subst = array('pattern' => array('/@GROUP@/'), 'replace' => array($grp));
                        print '<aside class="group_sidebar sidebar_box">' . DOKU_LF;
                        print p_sidebar_xhtml($group_sb,$pos,$subst) . DOKU_LF;
                        print '</aside>' . DOKU_LF;
                    }
                }
            }
            break;

        case 'index':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            print '<aside class="index_sidebar sidebar_box">' . DOKU_LF;
            print '  ' . p_index_xhtml($svID,$pos) . DOKU_LF;
            print '</aside>' . DOKU_LF;
            break;

        case 'toc':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            if(auth_quickaclcheck($svID) >= AUTH_READ) {
                $toc = tpl_toc(true);
                // replace ids to keep XHTML compliance
                if(!empty($toc)) {
                    $toc = preg_replace('/id="(.*?)"/', 'id="sb__' . $pos . '__\1"', $toc);
                    print '<nav class="toc_sidebar sidebar_box">' . DOKU_LF;
                    print ($toc);
                    print '</nav>' . DOKU_LF;
                }
            }
            break;

        case 'toolbox':

            if(tpl_getConf('hideactions') && !isset($_SERVER['REMOTE_USER'])) return;

            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                print '<div class="toolbox_sidebar sidebar_box">' . DOKU_LF;
                print '  <div class="level1">' . DOKU_LF;
                print '    <ul>' . DOKU_LF;
                print '      <li><div class="li">';
                tpl_actionlink('login');
                print '      </div></li>' . DOKU_LF;
                print '    </ul>' . DOKU_LF;
                print '  </div>' . DOKU_LF;
                print '</div>' . DOKU_LF;
            } else {
                $actions = array('admin',
                                 'revert',
                                 'edit',
                                 'history',
                                 'recent',
                                 'backlink',
                                 'media',
                                 'subscribe',
                                 'index',
                                 'login',
                                 'profile',
                                 'top');

                print '<div class="toolbox_sidebar sidebar_box">' . DOKU_LF;
                print '  <div class="level1">' . DOKU_LF;
                print '    <ul>' . DOKU_LF;

                foreach($actions as $action) {
                    if(!actionOK($action)) continue;
                    // start output buffering
                    if($action == 'edit') {
                        // check if new page button plugin is available
                        if(!plugin_isdisabled('npd') && ($npd =& plugin_load('helper', 'npd'))) {
                            $npb = $npd->html_new_page_button(true);
                            if($npb) {
                                print '    <li><div class="li">';
                                print $npb;
                                print '</div></li>' . DOKU_LF;
                            }
                        }
                    }
                    ob_start();
                    print '     <li><div class="li">';
                    if(tpl_actionlink($action)) {
                        print '</div></li>' . DOKU_LF;
                        ob_end_flush();
                    } else {
                        ob_end_clean();
                    }
                }

                print '    </ul>' . DOKU_LF;
                print '  </div>' . DOKU_LF;
                print '</div>' . DOKU_LF;
            }

            break;

        case 'trace':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            print '<nav class="trace_sidebar sidebar_box">' . DOKU_LF;
            print '  <h1>'.$lang['breadcrumb'].'</h1>' . DOKU_LF;
            print '  <div class="breadcrumbs">' . DOKU_LF;
            ($conf['youarehere'] != 1) ? tpl_breadcrumbs() : tpl_youarehere();
            print '  </div>' . DOKU_LF;
            print '</nav>' . DOKU_LF;
            break;

        case 'extra':
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            print '<aside class="extra_sidebar sidebar_box">' . DOKU_LF;
            @include(dirname(__FILE__).'/' . $pos .'_sidebar.html');
            print '</aside>' . DOKU_LF;
            break;

        default:
            if(tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) return;
            // check for user defined sidebars
            if(@file_exists(DOKU_TPLINC.'sidebars/'.$sb.'/sidebar.php')) {
                print '<aside class="'.$sb.'_sidebar sidebar_box">' . DOKU_LF;
                @require_once(DOKU_TPLINC.'sidebars/'.$sb.'/sidebar.php');
                print '</aside>' . DOKU_LF;
            }
            break;
    }

    // restore ID, REV and TOC
    $ID  = $svID;
    $REV = $svREV;
    $TOC = $svTOC;
}

/**
 * Removes the TOC of the sidebar pages and
 * shows a edit button if the user has enough rights
 *
 * TODO sidebar caching
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function p_sidebar_xhtml($sb,$pos,$subst=array()) {
    $data = p_wiki_xhtml($sb,'',false);
    if(!empty($subst)) {
        $data = preg_replace($subst['pattern'], $subst['replace'], $data);
    }
    if(auth_quickaclcheck($sb) >= AUTH_EDIT) {
        $data .= '<div class="secedit">'.html_btn('secedit',$sb,'',array('do'=>'edit','rev'=>'','post')).'</div>';
    }
    // strip TOC
    $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
    // replace headline ids for XHTML compliance
    $data = preg_replace('/(<h.*?><a.*?name=")(.*?)(".*?id=")(.*?)(">.*?<\/a><\/h.*?>)/','\1sb_'.$pos.'_\2\3sb_'.$pos.'_\4\5', $data);
    return ($data);
}

/**
 * Renders the Index
 *
 * copy of html_index located in /inc/html.php
 *
 * TODO update to new AJAX index possible?
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 */
function p_index_xhtml($ns,$pos) {
  require_once(DOKU_INC.'inc/search.php');
  global $conf;
  global $ID;
  $dir = $conf['datadir'];
  $ns  = cleanID($ns);
  #fixme use appropriate function
  if(empty($ns)){
    $ns = dirname(str_replace(':','/',$ID));
    if($ns == '.') $ns ='';
  }
  $ns  = utf8_encodeFN(str_replace(':','/',$ns));

  // extract only the headline
  preg_match('/<h1>.*?<\/h1>/', p_locale_xhtml('index'), $match);
  print preg_replace('#<h1(.*?id=")(.*?)(".*?)h1>#', '<h1\1sidebar_'.$pos.'_\2\3h1>', $match[0]);

  $data = array();
  search($data,$conf['datadir'],'search_index',array('ns' => $ns));

  print '<div id="' . $pos . '__index__tree">' . DOKU_LF;
  print html_buildlist($data,'idx','html_list_index','html_li_index');
  print '</div>' . DOKU_LF;
}

/**
 * Renders the Searchform
 * calls of tpl_searchform located in /inc/html.php
 * and adds a label
 *
 * @author Samuel Fischer <sf@notomorrow.de>
 */
function arctic_tpl_searchform(){
    #print '<label>' . DOKU_LF;
    tpl_searchform();
    #print '</label>' . DOKU_LF;
}


/**
 * searches for namespace sidebars
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function _getNsSb($id) {
    $pname = tpl_getConf('pagename');
    $ns_sb = '';
    $path  = explode(':', $id);
    $found = false;

    while(count($path) > 0) {
        $ns_sb = implode(':', $path).':'.$pname;
        if(@page_exists($ns_sb)) return $ns_sb;
        array_pop($path);
    }

    // nothing found
    return false;
}

/**
 * Checks whether the sidebar should be hidden or not
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function arctic_tpl_sidebar_hide() {
    global $ACT;
    $act_hide = array( 'edit', 'preview', 'admin', 'conflict', 'draft', 'recover', 'media' );
    if(in_array($ACT, $act_hide)) {
        return true;
    } else {
        return false;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
