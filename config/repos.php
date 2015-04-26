<?php

$mediawiki_repo = array();
$mediawiki_repo['folder'] = 'MediaWiki';
$mediawiki_repo['git'] = 'https://gerrit.wikimedia.org/r/p/mediawiki/core.git';
$mediawiki_repo['branch'] = 'remotes/origin/REL1_24';
$mediawiki_repo['location'] = '';

$repos = array();

$repos['unccskin'] = array();
$repos['unccskin']['folder'] = 'unc-charlotte-skin';
$repos['unccskin']['git'] = 'https://github.com/clas-web/mediawiki-unc-charlotte-skin.git';
$repos['unccskin']['branch'] = 'master';
$repos['unccskin']['location'] = 'skins/unc-charlotte/';

$repos['ldap'] = array();
$repos['ldap']['folder'] = 'LdapAuthentication';
$repos['ldap']['git'] = 'https://git.wikimedia.org/git/mediawiki/extensions/LdapAuthentication.git';
$repos['ldap']['branch'] = 'master';
$repos['ldap']['location'] = 'extensions/LdapAuthentication/';

$repos['wysiwyg'] = array();
$repos['wysiwyg']['folder'] = 'wysiwyg';
$repos['wysiwyg']['git'] = 'https://github.com/Mediawiki-wysiwyg/WYSIWYG-CKeditor';
$repos['wysiwyg']['branch'] = 'master';
$repos['wysiwyg']['location'] = 'extensions/';
