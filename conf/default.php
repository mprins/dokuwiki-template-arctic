<?php

/**
 * Default configuration for the arctic template
 *
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author      Michael Klier <chi@chimeric.de>
 * @author      Mark C. Prins <mprins@users.sf.net>
 */

// hide all wiki related actions for non-logged-in users
$conf['hideactions'] = 0;
// defines the position of the search form when 2 sidebars are used
$conf['search'] = 'left';
// enable/disable sidebar
$conf['sidebar'] = 'left';
// the pagename for sidebars inside namespaces
$conf['pagename'] = 'sidebar';
// namespace to look for namespace of logged-in user
$conf['user_sidebar_namespace'] = 'user';
// namespace to look for groups-namespaces
$conf['group_sidebar_namespace'] = 'group';
// show trace at top of the page
$conf['trace'] = 1;
// show main sidebar on all namespaces
$conf['main_sidebar_always'] = 1;
// use buttons instead of links
$conf['wiki_actionlinks'] = 'links';
// defines the content of the left sidebar
$conf['left_sidebar_content'] = 'main,user,group,namespace';
// defines the order of the left sidebar content
$conf['left_sidebar_order'] = 'main,namespace,user,group';
// defines the content of the right sidebar
$conf['right_sidebar_content'] = 'main,user,group,namespace';
// defines the order of the right sidebar content
$conf['right_sidebar_order'] = 'main,namespace,user,group';
// don't show sidebars for logged-out users at all
$conf['closedwiki'] = 0;
// add opengraph namespace prefixes to head section
$conf['opengraphheading'] = 1;
