<?php
/**
 * DokuWiki Arctic Template.
 *
 * This is the template you need to change for the overall look
 * of DokuWiki.
 *
 * You should leave the doctype at the very top - It should
 * always be the very first line of a document.
 *
 * @author Mark C. Prins <mprins@users.sf.net>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @link   http://www.dokuwiki.org/template:arctic
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) {
    die();
}

global $ACT;

// include custom arctic template functions
require_once(__DIR__ .'/tpl_functions.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $conf['lang']?>" id="document" dir="<?php echo $lang['direction']?>">
<head<?php if (tpl_getConf('opengraphheading')) {
    ?> prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article# fb: http://ogp.me/ns/fb# place: http://ogp.me/ns/place# book: http://ogp.me/ns/book#"<?php
     } ?>>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>
    <?php tpl_pagetitle()?>
    [<?php echo strip_tags($conf['title'])?>]
  </title>

  <?php tpl_metaheaders()?>

  <?php echo tpl_favicon(['favicon', 'mobile', 'generic']) ?>

  <?php tpl_includeFile('meta.html') ?>

</head>
<body>

<div id="skiplinks" class="skiplinks">
  <a href="#dokuwiki__content" class="skiplink"><?php echo $lang['skip_to_content']; ?></a>
</div>

<?php tpl_includeFile('topheader.html') ?>
<div id="wrapper" class='<?php echo $ACT ?>'>
  <div class="dokuwiki">

    <?php html_msgarea()?>

    <header class="stylehead">
      <div class="header">
        <div class="pagename">
          [[<?php tpl_link(wl($ID, 'do=backlink'), tpl_pagetitle($ID, true))?>]]
        </div>
        <div class="logo">
          <?php tpl_link(wl(), $conf['title'], 'id="dokuwiki__top" accesskey="h" title="[ALT+H]"')?>
        </div>
      </div>

      <?php if (tpl_getConf('trace')) {?>
      <div class="breadcrumbs">
            <?php ($conf['youarehere'] != 1) ? tpl_breadcrumbs() : tpl_youarehere();?>
      </div>
      <?php } ?>

      <?php tpl_includeFile('header.html') ?>
    </header>

      <?php if (!$toolb) { ?>
            <?php if (!tpl_getConf('hideactions') || tpl_getConf('hideactions') && isset($_SERVER['REMOTE_USER'])) { ?>
      <div class="bar" id="bar__top">
        <div class="bar-left">
                <?php
                if (!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                    switch (tpl_getConf('wiki_actionlinks')) {
                        case ('buttons'):
                        // check if new page button plugin is available
                            if (!plugin_isdisabled('npd') && ($npd =& plugin_load('helper', 'npd'))) {
                                $npd->html_new_page_button();
                            }
                            echo (new \dokuwiki\Menu\Item\Edit())->asHtmlButton();
                            break;
                        case ('links'):
                      // check if new page button plugin is available
                            if (!plugin_isdisabled('npd') && ($npd =& plugin_load('helper', 'npd'))) {
                                $npd->html_new_page_button();
                            }
                            echo (new \dokuwiki\Menu\Item\Edit())->asHtmlLink('action ', false);
                            break;
                    }
                }
                ?>
        </div>
        <div class="bar-right">
                <?php
                switch (tpl_getConf('wiki_actionlinks')) {
                    case ('buttons'):
                        if (!tpl_getConf('closedwiki') ||
                            (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                            echo (new \dokuwiki\Menu\Item\Admin())->asHtmlButton();
//                            echo (new \dokuwiki\Menu\Item\Revert())->asHtmlButton();
                            echo (new \dokuwiki\Menu\Item\Profile())->asHtmlButton();
                            echo (new \dokuwiki\Menu\Item\Recent())->asHtmlButton();
                            echo (new \dokuwiki\Menu\Item\Index())->asHtmlButton();
                            echo (new \dokuwiki\Menu\Item\Login())->asHtmlButton();
                            if (tpl_getConf('sidebar') === 'none') {
                                arctic_tpl_searchform();
                            }
                        } else {
                            echo (new \dokuwiki\Menu\Item\Login())->asHtmlButton();
                        }
                        break;
                    case ('links'):
                        if (!tpl_getConf('closedwiki') ||
                            (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) {
                            echo (new \dokuwiki\Menu\Item\Admin())->asHtmlLink('action ', false);
//                            echo (new \dokuwiki\Menu\Item\Revert())->asHtmlLink('action ', false);
                            echo (new \dokuwiki\Menu\Item\Profile())->asHtmlLink('action ', false);
                            echo (new \dokuwiki\Menu\Item\Recent())->asHtmlLink('action ', false);
                            echo (new \dokuwiki\Menu\Item\Index())->asHtmlLink('action ', false);
                            echo (new \dokuwiki\Menu\Item\Login())->asHtmlLink('action ', false);
                            if (tpl_getConf('sidebar') === 'none') {
                                arctic_tpl_searchform();
                            }
                        } else {
                            echo (new \dokuwiki\Menu\Item\Login())->asHtmlLink('action ', false);
                        }
                        break;
                }
                ?>
        </div>
    </div>
            <?php } ?>
      <?php } ?>

    <?php tpl_includeFile('pageheader.html') ?>

    <?php flush()?>

    <?php if (tpl_getConf('sidebar') === 'left') { ?>
        <?php if (!arctic_tpl_sidebar_hide()) { ?>
        <div class="left_sidebar">
            <?php arctic_tpl_searchform() ?>
            <?php arctic_tpl_sidebar('left') ?>
        </div>
        <main class="right_page" id="dokuwiki__content" tabindex="-1">
            <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </main>
        <?php } else { ?>
        <main class="page" id="dokuwiki__content" tabindex="-1">
            <?php tpl_content()?>
        </main>
        <?php } ?>

    <?php } elseif (tpl_getConf('sidebar') === 'right') { ?>
        <?php if (!arctic_tpl_sidebar_hide()) { ?>
        <main class="left_page" id="dokuwiki__content" tabindex="-1">
            <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </main>
        <div class="right_sidebar">
            <?php arctic_tpl_searchform() ?>
            <?php arctic_tpl_sidebar('right') ?>
        </div>
        <?php } else { ?>
        <main class="page" id="dokuwiki__content" tabindex="-1">
            <?php tpl_content() ?>
        </main>
        <?php }?>

    <?php } elseif (tpl_getConf('sidebar') === 'both') { ?>
        <?php if (!arctic_tpl_sidebar_hide()) { ?>
        <div class="left_sidebar">
            <?php if (tpl_getConf('search') === 'left') {
                arctic_tpl_searchform();
            } ?>
            <?php arctic_tpl_sidebar('left') ?>
        </div>
        <main class="center_page" id="dokuwiki__content" tabindex="-1">
            <?php ($notoc) ? tpl_content(false) : tpl_content() ?>
        </main>
        <div class="right_sidebar">
            <?php if (tpl_getConf('search') === 'right') {
                arctic_tpl_searchform();
            } ?>
            <?php arctic_tpl_sidebar('right') ?>
        </div>
        <?php } else { ?>
        <main class="page" id="dokuwiki__content" tabindex="-1">
            <?php tpl_content()?>
        </main>
        <?php }?>

    <?php } elseif (tpl_getConf('sidebar') === 'none') { ?>
      <main class="page" id="dokuwiki__content" tabindex="-1">
        <?php tpl_content() ?>
      </main>
    <?php } ?>

      <footer class="stylefoot">
        <div class="meta">
          <div class="user">
          <?php tpl_userinfo()?>
          </div>
          <div class="doc">
          <?php tpl_pageinfo()?>
          </div>
        </div>
      </footer>

    <div class="clearer"></div>

    <?php flush()?>

    <?php if (!$toolb) { ?>
        <?php if (!tpl_getConf('hideactions') || tpl_getConf('hideactions') && isset($_SERVER['REMOTE_USER'])) { ?>
            <?php if (!tpl_getConf('closedwiki') || (tpl_getConf('closedwiki') && isset($_SERVER['REMOTE_USER']))) { ?>

    <div class="bar" id="bar__bottom">
      <div class="bar-left">
                <?php
                switch (tpl_getConf('wiki_actionlinks')) {
                    case ('buttons'):
                        echo (new \dokuwiki\Menu\Item\Edit())->asHtmlButton();
                        echo (new \dokuwiki\Menu\Item\Recent())->asHtmlButton();
                        break;
                    case ('links'):
                        echo (new \dokuwiki\Menu\Item\Edit())->asHtmlLink('action ', false);
                        echo (new \dokuwiki\Menu\Item\Recent())->asHtmlLink('action ', false);
                        break;
                }
                ?>
      </div>

      <div class="bar-right">
                <?php
                switch (tpl_getConf('wiki_actionlinks')) {
                    case ('buttons'):
                        echo (new \dokuwiki\Menu\Item\Media())->asHtmlButton();
                        echo (new \dokuwiki\Menu\Item\Subscribe())->asHtmlButton();
                        echo (new \dokuwiki\Menu\Item\Top())->asHtmlButton();
                        break;
                    case ('links'):
                        echo (new \dokuwiki\Menu\Item\Media())->asHtmlLink('action ', false);
                        echo (new \dokuwiki\Menu\Item\Subscribe())->asHtmlLink('action ', false);
                        echo (new \dokuwiki\Menu\Item\Top())->asHtmlLink('action ', false);
                        break;
                }
                ?>
      </div>
    </div>

    <div class="clearer"></div>
            <?php } ?>
        <?php } ?>
    <?php } ?>

    <?php tpl_includeFile('footer.html') ?>

  </div>
</div>

<div class="no"><?php /* provide DokuWiki housekeeping, required in all templates */ tpl_indexerWebBug()?></div>
</body>
</html>
