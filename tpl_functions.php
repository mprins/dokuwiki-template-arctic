<?php

/**
 * DokuWiki Template Arctic Functions.
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Klier <chi@chimeric.de>
 */

use dokuwiki\Menu\AbstractMenu;
use dokuwiki\Menu\Item\AbstractItem;
use dokuwiki\Menu\Item\Login;

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_LF')) {
    define('DOKU_LF', "\n");
}

// load sidebar contents
$sbl = explode(',', tpl_getConf('left_sidebar_content'));
$sbr = explode(',', tpl_getConf('right_sidebar_content'));
$sbpos = tpl_getConf('sidebar');

// set notoc option and toolbar regarding the sidebar setup
switch ($sbpos) {
    case 'both':
        $notoc = (in_array('toc', $sbl) || in_array('toc', $sbr));
        $toolb = (in_array('toolbox', $sbl) || in_array('toolbox', $sbr));
        break;
    case 'left':
        $notoc = in_array('toc', $sbl);
        $toolb = in_array('toolbox', $sbl);
        break;
    case 'right':
        $notoc = in_array('toc', $sbr);
        $toolb = in_array('toolbox', $sbr);
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
function arctic_tpl_sidebar($pos): void
{
    $sb_order = ($pos === 'left') ?
        explode(',', tpl_getConf('left_sidebar_order')) :
        explode(',', tpl_getConf('right_sidebar_order'));
    $sb_content = ($pos === 'left') ?
        explode(',', tpl_getConf('left_sidebar_content')) :
        explode(',', tpl_getConf('right_sidebar_content'));

    // process contents by given order
    foreach ($sb_order as $sb) {
        if (in_array($sb, $sb_content)) {
            $key = array_search($sb, $sb_content);
            unset($sb_content[$key]);
            arctic_tpl_sidebar_dispatch($sb, $pos);
        }
    }

    // check for left content not specified by order
    if (is_array($sb_content) && ($sb_content !== []) > 0) {
        foreach ($sb_content as $sb) {
            arctic_tpl_sidebar_dispatch($sb, $pos);
        }
    }
}

/**
 * Dispatches the given sidebar type to return the right content
 *
 * @author Michael Klier <chi@chimeric.de>
 * @author Mark C. Prins <mprins@users.sf.net>
 */
function arctic_tpl_sidebar_dispatch($sb, $pos): void
{
    global $lang;
    global $conf;
    global $ID;
    global $REV;
    global $INFO;
    global $TOC;

    $svID = $ID;   // save current ID
    $svREV = $REV;  // save current REV
    $svTOC = $TOC;  // save current TOC

    $pname = tpl_getConf('pagename');

    switch ($sb) {
        case 'main':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            $main_sb = $pname;
            if (@page_exists($main_sb) && auth_quickaclcheck($main_sb) >= AUTH_READ) {
                $always = tpl_getConf('main_sidebar_always');
                if ($always || (!$always && !getNS($ID))) {
                    echo '<aside class="main_sidebar sidebar_box">' . DOKU_LF;
                    echo p_sidebar_xhtml($main_sb, $pos) . DOKU_LF;
                    echo '</aside>' . DOKU_LF;
                }
            } elseif (!@page_exists($main_sb) && auth_quickaclcheck($main_sb) >= AUTH_CREATE) {
                if (@file_exists(tpl_incdir() . 'lang/' . $conf['lang'] . '/nosidebar.txt')) {
                    $out = p_render(
                        'xhtml',
                        p_get_instructions(io_readFile(tpl_incdir() . 'lang/' . $conf['lang'] . '/nosidebar.txt')),
                        $info
                    );
                } else {
                    $out = p_render(
                        'xhtml',
                        p_get_instructions(io_readFile(tpl_incdir() . 'lang/en/nosidebar.txt')),
                        $info
                    );
                }
                $link = '<a href="' . wl($pname) . '" class="wikilink2">' . $pname . '</a>' . DOKU_LF;
                echo '<aside class="main_sidebar sidebar_box">' . DOKU_LF;
                echo str_replace('LINK', $link, $out);
                echo '</aside>' . DOKU_LF;
            }
            break;

        case 'namespace':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            $user_ns = tpl_getConf('user_sidebar_namespace');
            $group_ns = tpl_getConf('group_sidebar_namespace');
            if (!preg_match("/^" . $user_ns . ":.*?$|^" . $group_ns . ":.*?$/", $svID)) {
                // skip group/user sidebars and current ID
                $ns_sb = _getNsSb($svID);
                if ($ns_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
                    echo '<aside class="namespace_sidebar sidebar_box">' . DOKU_LF;
                    echo p_sidebar_xhtml($ns_sb, $pos) . DOKU_LF;
                    echo '</aside>' . DOKU_LF;
                }
            }
            break;

        case 'user':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            $user_ns = tpl_getConf('user_sidebar_namespace');
            if (isset($INFO['userinfo']['name'])) {
                $user = $_SERVER['REMOTE_USER'];
                $user_sb = $user_ns . ':' . $user . ':' . $pname;
                if (@page_exists($user_sb)) {
                    $subst = ['pattern' => ['/@USER@/'], 'replace' => [$user]];
                    echo '<aside class="user_sidebar sidebar_box">' . DOKU_LF;
                    echo p_sidebar_xhtml($user_sb, $pos, $subst) . DOKU_LF;
                    echo '</aside>';
                }
                // check for namespace sidebars in user namespace too
                if (preg_match('/' . $user_ns . ':' . $user . ':.*/', $svID)) {
                    $ns_sb = _getNsSb($svID);
                    if ($ns_sb && $ns_sb != $user_sb && auth_quickaclcheck($ns_sb) >= AUTH_READ) {
                        echo '<aside class="namespace_sidebar sidebar_box">' . DOKU_LF;
                        echo p_sidebar_xhtml($ns_sb, $pos) . DOKU_LF;
                        echo '</aside>' . DOKU_LF;
                    }
                }
            }
            break;

        case 'group':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            $group_ns = tpl_getConf('group_sidebar_namespace');
            if (isset($INFO['userinfo']['name'], $INFO['userinfo']['grps'])) {
                foreach ($INFO['userinfo']['grps'] as $grp) {
                    $group_sb = $group_ns . ':' . $grp . ':' . $pname;
                    if (@page_exists($group_sb) && auth_quickaclcheck(cleanID($group_sb)) >= AUTH_READ) {
                        $subst = ['pattern' => ['/@GROUP@/'], 'replace' => [$grp]];
                        echo '<aside class="group_sidebar sidebar_box">' . DOKU_LF;
                        echo p_sidebar_xhtml($group_sb, $pos, $subst) . DOKU_LF;
                        echo '</aside>' . DOKU_LF;
                    }
                }
            }
            break;

        case 'index':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            echo '<aside class="index_sidebar sidebar_box">' . DOKU_LF;
            echo '  ' . p_index_xhtml($svID, $pos) . DOKU_LF;
            echo '</aside>' . DOKU_LF;
            break;

        case 'toc':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            if (auth_quickaclcheck($svID) >= AUTH_READ) {
                $toc = tpl_toc(true);
                // replace ids to keep XHTML compliance
                if (!empty($toc)) {
                    $toc = preg_replace('/id="(.*?)"/', 'id="sb__' . $pos . '__\1"', $toc);
                    echo '<nav class="toc_sidebar sidebar_box">' . DOKU_LF;
                    echo $toc;
                    echo '</nav>' . DOKU_LF;
                }
            }
            break;

        case 'toolbox':
            if (tpl_getConf('hideactions') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }

            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                echo '<div class="toolbox_sidebar sidebar_box">' . DOKU_LF;
                echo '  <div class="level1">' . DOKU_LF;
                echo '    <ul>' . DOKU_LF;
                echo '      <li><div class="li">';
                echo (new Login())->asHtmlLink('action ', false);
                echo '      </div></li>' . DOKU_LF;
                echo '    </ul>' . DOKU_LF;
                echo '  </div>' . DOKU_LF;
                echo '</div>' . DOKU_LF;
            } else {
                /** @var AbstractItem[] $items */
                $items = (new class extends AbstractMenu {
                    protected $view = 'page';
                    protected $types = [
                        'Admin',
                        'Revert',
                        'Edit',
                        'Revisions',
                        'Recent',
                        'Backlink',
                        'Media',
                        'Subscribe',
                        'Index',
                        'Login',
                        'Profile',
                        'Top'];
                })->getItems();

                echo '<div class="toolbox_sidebar sidebar_box">' . DOKU_LF;
                echo '  <div class="level1">' . DOKU_LF;
                echo '  <h2>toolbox</h2>' . DOKU_LF;
                echo '    <ul>' . DOKU_LF;

                // start output buffering
                ob_start();
                foreach ($items as $item) {
                    if (!actionOK($item->getType())) {
                        continue;
                    }
                    echo '     <li><div class="li">';
                    echo $item->asHtmlLink('action ', false);
                    echo '     </div></li>' . DOKU_LF;
                }
                ob_end_flush();

                echo '    </ul>' . DOKU_LF;
                echo '  </div>' . DOKU_LF;
                echo '</div>' . DOKU_LF;
            }

            break;

        case 'trace':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            echo '<nav class="trace_sidebar sidebar_box">' . DOKU_LF;
            echo '  <h1>' . $lang['breadcrumb'] . '</h1>' . DOKU_LF;
            echo '  <div class="breadcrumbs">' . DOKU_LF;
            ($conf['youarehere'] != 1) ? tpl_breadcrumbs() : tpl_youarehere();
            echo '  </div>' . DOKU_LF;
            echo '</nav>' . DOKU_LF;
            break;

        case 'extra':
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            echo '<aside class="extra_sidebar sidebar_box">' . DOKU_LF;
            @include(__DIR__ . '/' . $pos . '_sidebar.html');
            echo '</aside>' . DOKU_LF;
            break;

        default:
            if (tpl_getConf('closedwiki') && !isset($_SERVER['REMOTE_USER'])) {
                return;
            }
            // check for user defined sidebars
            if (@file_exists(tpl_incdir() . 'sidebars/' . $sb . '/sidebar.php')) {
                echo '<aside class="' . $sb . '_sidebar sidebar_box">' . DOKU_LF;
                @require_once(tpl_incdir() . 'sidebars/' . $sb . '/sidebar.php');
                echo '</aside>' . DOKU_LF;
            }
            break;
    }

    // restore ID, REV and TOC
    $ID = $svID;
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
function p_sidebar_xhtml($sb, $pos, $subst = []): array|string|null
{
    $data = p_wiki_xhtml($sb, '', false);
    if (!empty($subst)) {
        $data = preg_replace($subst['pattern'], $subst['replace'], $data);
    }
    if (auth_quickaclcheck($sb) >= AUTH_EDIT) {
        $data .= '<div class="secedit">' . html_btn(
            'secedit',
            $sb,
            '',
            ['do' => 'edit', 'rev' => '', 'post']
        ) . '</div>';
    }
    // strip TOC
    $data = preg_replace('/<div class="toc">.*?(<\/div>\n<\/div>)/s', '', $data);
    // replace headline ids for XHTML compliance
    $data = preg_replace(
        '/(<h.*?><a.*?name=")(.*?)(".*?id=")(.*?)(">.*?<\/a><\/h.*?>)/',
        '\1sb_' . $pos . '_\2\3sb_' . $pos . '_\4\5',
        $data
    );
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
function p_index_xhtml($ns, $pos): void
{
    require_once(DOKU_INC . 'inc/search.php');
    global $conf;
    global $ID;
    $ns = cleanID($ns);
    #fixme use appropriate function
    if (empty($ns)) {
        $ns = dirname(str_replace(':', '/', $ID));
        if ($ns === '.') {
            $ns = '';
        }
    }
    $ns = utf8_encodeFN(str_replace(':', '/', $ns));

    // extract only the headline
    preg_match('/<h1>.*?<\/h1>/', p_locale_xhtml('index'), $match);
    echo preg_replace('#<h1(.*?id=")(.*?)(".*?)h1>#', '<h1\1sidebar_' . $pos . '_\2\3h1>', $match[0]);

    $data = [];
    search($data, $conf['datadir'], 'search_index', ['ns' => $ns]);

    echo '<div id="' . $pos . '__index__tree">' . DOKU_LF;
    echo html_buildlist($data, 'idx', 'html_list_index', 'html_li_index');
    echo '</div>' . DOKU_LF;
}


/**
 * searches for namespace sidebars
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function _getNsSb($id): bool|string
{
    $pname = tpl_getConf('pagename');
    $path = explode(':', $id);
    while ($path !== []) {
        $ns_sb = implode(':', $path) . ':' . $pname;
        if (@page_exists($ns_sb)) {
            return $ns_sb;
        }
        array_pop($path);
    }

    return false;
}

/**
 * Checks whether the sidebar should be hidden or not
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function arctic_tpl_sidebar_hide(): bool
{
    global $ACT;
    $act_hide = ['edit', 'preview', 'admin', 'conflict', 'draft', 'recover', 'media'];
    if (in_array($ACT, $act_hide)) {
        return true;
    }

    return false;
}
