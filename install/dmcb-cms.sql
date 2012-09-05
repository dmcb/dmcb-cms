-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2012 at 10:28 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dmcb-cms`
--

-- --------------------------------------------------------

--
-- Table structure for table `acls`
--

CREATE TABLE IF NOT EXISTS `acls` (
  `userid` int(10) unsigned NOT NULL,
  `roleid` int(10) unsigned NOT NULL,
  `controller` varchar(5) NOT NULL,
  `attachedid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`controller`,`attachedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acls`
--

INSERT INTO `acls` (`userid`, `roleid`, `controller`, `attachedid`) VALUES
(1, 1, 'site', 0);

-- --------------------------------------------------------

--
-- Table structure for table `acls_functions`
--

CREATE TABLE IF NOT EXISTS `acls_functions` (
  `functionid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `controller` varchar(20) NOT NULL,
  `function` varchar(50) NOT NULL,
  `functionof` int(10) unsigned DEFAULT NULL,
  `enabled` int(1) NOT NULL,
  `guestpossible` int(1) NOT NULL,
  `ownerpossible` int(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`functionid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=101 ;

--
-- Dumping data for table `acls_functions`
--

INSERT INTO `acls_functions` (`functionid`, `controller`, `function`, `functionof`, `enabled`, `guestpossible`, `ownerpossible`, `name`) VALUES
(1, 'page', 'addpage', NULL, 0, 0, 0, 'Add a child page'),
(2, 'page', 'addpost', NULL, 0, 0, 0, 'Add posts to page'),
(3, 'page', 'attachments', NULL, 1, 0, 0, 'Manage attachments'),
(4, 'page', 'blocks', 5, 1, 0, 0, 'Manage blocks'),
(5, 'page', 'edit', NULL, 1, 0, 0, 'Edit page'),
(6, 'page', 'permissions', NULL, 1, 0, 0, 'Manage permissions'),
(7, 'page', 'templates', 5, 0, 0, 0, 'Edit page post template'),
(8, 'page', 'theme', NULL, 0, 0, 0, 'Change page theme and CSS'),
(9, 'post', 'addcomment', NULL, 1, 1, 1, 'Add comments'),
(10, 'post', 'attachments', NULL, 1, 0, 1, 'Manage attachments'),
(11, 'post', 'deletecomment', 9, 1, 0, 1, 'Delete comment'),
(12, 'post', 'edit', NULL, 1, 0, 1, 'Edit post'),
(13, 'post', 'event', NULL, 1, 0, 1, 'Add and edit events'),
(14, 'post', 'permissions', NULL, 1, 0, 1, 'Manage permissions'),
(15, 'post', 'reportcomment', 9, 1, 1, 1, 'Report abusive comments'),
(16, 'post', 'taguser', NULL, 1, 0, 1, 'Tag users to a post'),
(17, 'post', 'theme', NULL, 1, 0, 1, 'Override CSS/Javascript'),
(18, 'profile', 'add', NULL, 1, 0, 0, 'Have profile'),
(19, 'profile', 'addpost', 18, 1, 0, 0, 'Add posts'),
(20, 'profile', 'edit', NULL, 1, 0, 0, 'Edit other profiles'),
(21, 'profile', 'message', 23, 1, 0, 0, 'Message users'),
(22, 'profile', 'twitter', 18, 1, 0, 0, 'Set twitter feed'),
(23, 'profile', 'view', NULL, 1, 1, 0, 'View profile'),
(24, 'site', 'add_users', 32, 1, 0, 0, 'Add new users'),
(25, 'site', 'change_role', 32, 1, 0, 0, 'Change user role'),
(26, 'site', 'change_status', 32, 1, 0, 0, 'Change user status'),
(27, 'site', 'mail_users', 32, 1, 0, 0, 'Mail users'),
(28, 'site', 'manage_activity', NULL, 1, 0, 0, 'Manage activity'),
(29, 'site', 'manage_content', NULL, 1, 0, 0, 'Manage site-wide content'),
(30, 'site', 'manage_pages', NULL, 1, 0, 0, 'Manage pages'),
(31, 'site', 'manage_security', NULL, 1, 0, 0, 'Manage security'),
(32, 'site', 'manage_users', NULL, 1, 0, 0, 'Manage users'),
(33, 'site', 'search', NULL, 1, 1, 0, 'Search the site'),
(34, 'site', 'set_password', 32, 1, 0, 0, 'Set user password'),
(35, 'site', 'set_subscription', 32, 1, 0, 0, 'Set user subscription'),
(36, 'site', 'subscribe', NULL, 0, 0, 0, 'Order a subscription');

-- --------------------------------------------------------

--
-- Table structure for table `acls_roles`
--

CREATE TABLE IF NOT EXISTS `acls_roles` (
  `roleid` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `internal` int(1) NOT NULL,
  `custom` int(1) NOT NULL,
  PRIMARY KEY (`roleid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `acls_roles`
--

INSERT INTO `acls_roles` (`roleid`, `role`, `internal`, `custom`) VALUES
(1, 'administrator', 0, 0),
(2, 'contributor', 0, 0),
(3, 'moderator', 0, 0),
(4, 'member', 0, 0),
(5, 'guest', 1, 0),
(6, 'owner', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `acls_roles_privileges`
--

CREATE TABLE IF NOT EXISTS `acls_roles_privileges` (
  `functionid` int(10) unsigned NOT NULL,
  `roleid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`functionid`,`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `acls_roles_privileges`
--

INSERT INTO `acls_roles_privileges` (`functionid`, `roleid`) VALUES
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(9, 1),
(9, 2),
(9, 3),
(9, 4),
(9, 6),
(10, 1),
(10, 6),
(11, 1),
(11, 3),
(12, 1),
(12, 6),
(13, 1),
(14, 1),
(15, 1),
(15, 3),
(16, 1),
(16, 6),
(17, 1),
(17, 6),
(18, 1),
(18, 2),
(18, 3),
(18, 4),
(19, 1),
(19, 2),
(19, 3),
(19, 4),
(20, 1),
(21, 1),
(21, 2),
(21, 3),
(21, 4),
(22, 1),
(22, 2),
(22, 3),
(22, 4),
(23, 1),
(23, 2),
(23, 3),
(23, 4),
(23, 5),
(24, 1),
(24, 2),
(25, 1),
(26, 1),
(26, 2),
(26, 3),
(27, 1),
(27, 2),
(28, 1),
(28, 3),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(32, 2),
(32, 3),
(33, 1),
(33, 2),
(33, 3),
(33, 4),
(33, 5),
(34, 1),
(34, 2),
(34, 3),
(35, 1),
(35, 2),
(35, 3);

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE IF NOT EXISTS `blocks` (
  `function` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `rsspossible` int(1) NOT NULL DEFAULT '0',
  `paginationpossible` int(1) NOT NULL DEFAULT '0',
  `enabled` int(1) NOT NULL,
  PRIMARY KEY (`function`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`function`, `name`, `rsspossible`, `paginationpossible`, `enabled`) VALUES
('authors', 'Authors listing', 1, 1, 1),
('authors_new', 'Newest authors listing', 1, 1, 0),
('breadcrumb', 'Bread crumb navigation', 0, 0, 1),
('categories', 'Categories', 0, 0, 1),
('comments', 'Comments listing', 1, 1, 0),
('events', 'Events listing', 1, 1, 0),
('facebook', 'Facebook like button', 0, 0, 0),
('files', 'Files listing', 0, 0, 0),
('flickr', 'Flickr feed', 0, 0, 0),
('form', 'CSRF form code', 0, 0, 0),
('image', 'Featured page image', 0, 0, 0),
('menu', 'Menu of neighbouring pages', 0, 0, 1),
('posts', 'Posts listing', 1, 1, 1),
('scrape', 'Scrape HTML from page', 0, 0, 0),
('signup_mailinglist', 'Mailing list sign up', 0, 0, 0),
('twitter', 'Twitter feed', 0, 0, 0),
('user_displayname', 'Display name of signed on user', 0, 0, 0),
('user_email', 'Email address of signed on user', 0, 0, 0),
('wall', 'Anonymous wall postings', 0, 1, 0),
('wrapper', 'Visibility wrapper', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `blocks_defaults`
--

CREATE TABLE IF NOT EXISTS `blocks_defaults` (
  `blockinstanceid` int(10) unsigned NOT NULL,
  `pageid` int(10) unsigned NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`pageid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blocks_instances`
--

CREATE TABLE IF NOT EXISTS `blocks_instances` (
  `blockinstanceid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pageid` int(10) unsigned NOT NULL,
  `function` varchar(20) NOT NULL,
  `title` varchar(20) NOT NULL,
  `feedback` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`blockinstanceid`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `blocks_instances_values`
--

CREATE TABLE IF NOT EXISTS `blocks_instances_values` (
  `blockinstanceid` int(10) unsigned NOT NULL,
  `variablename` varchar(100) NOT NULL,
  `value` varchar(2000) NOT NULL,
  PRIMARY KEY (`blockinstanceid`,`variablename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blocks_variables`
--

CREATE TABLE IF NOT EXISTS `blocks_variables` (
  `function` varchar(20) NOT NULL,
  `variablename` varchar(100) NOT NULL,
  `variabledescription` varchar(1000) NOT NULL,
  `pattern` varchar(100) NOT NULL,
  `rules` varchar(100) DEFAULT NULL,
  `list` int(1) unsigned NOT NULL,
  PRIMARY KEY (`function`,`variablename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blocks_variables`
--

INSERT INTO `blocks_variables` (`function`, `variablename`, `variabledescription`, `pattern`, `rules`, `list`) VALUES
('authors', 'detail', 'Set the level of detail on the authors.', 'listing|full', NULL, 0),
('authors', 'limit', 'Set the amount of authors to show at once.', '*', 'integer', 0),
('authors', 'sort', 'Sort authors by the date they contributed or by their name.', 'alphabetical|chronological', NULL, 0),
('authors_new', 'detail', 'Set the level of detail on the authors.', 'listing|full', NULL, 0),
('authors_new', 'limit', 'Set the amount of authors to show at once.', '*', 'integer', 0),
('breadcrumb', 'home', 'Text for the home button, leave empty if you do not wish to have a home button.', '*', NULL, 0),
('breadcrumb', 'page', 'Select the page to generate bread crumb navigation from.', 'current|*', NULL, 0),
('breadcrumb', 'placeholders', 'Show menu items that are place holders or external links.', 'yes|no', NULL, 0),
('categories', 'detail', 'Set the style of the categories.', 'listing|tagcloud', NULL, 0),
('categories', 'featured', 'Show only categories containing featured posts, that don''t have featured posts or all posts.', 'yes|no|only', NULL, 0),
('categories', 'page', 'Select the page to draw categories from. ''Current'' shows only categories of posts on this page, ''No page'' shows user created post categories, or you can select your own page.', 'all|current|nopage|*', NULL, 1),
('comments', 'detail', 'Set the level of detail on the comments.', 'small|large', NULL, 0),
('comments', 'limit', 'Set the amount of comments to show at once.', '*', 'integer', 0),
('comments', 'page', 'Select the page to draw comments from. ''Current'' shows only comments from posts on this page, ''No page'' shows user created post comments, or you can select your own page.', 'all|current|nopage|*', NULL, 1),
('comments', 'post', 'Select comments only from a specific post. Setting this value overrides the page value.', '*', NULL, 1),
('events', 'detail', 'Set the level of detail on the events.', 'listing|preview|full', NULL, 0),
('events', 'featured', 'Show only events that are featured, aren''t featured or all events.', 'yes|no|only', NULL, 0),
('events', 'limit', 'Set the amount of events to show at once.', '*', 'integer', 0),
('events', 'page', 'Select the page to draw events from. ''Current'' shows only events from posts on this page, ''No page'' shows user created post events, or you can select your own page.', 'all|current|nopage|*', NULL, 1),
('events', 'page_children', 'Include events from all children of the specified page.', 'no|yes', NULL, 0),
('events', 'timeline', 'Show events that are upcoming or events have already been held.', 'upcoming|previous', NULL, 0),
('files', 'page', 'Select the page to draw files from. ''Current'' shows only files on this page, or you can select your own page.', 'current|*', NULL, 0),
('flickr', 'limit', 'Set the amount of images to show at once.', '*', 'integer', 0),
('flickr', 'query', 'This is the query string of the flickr feed, e.g. ''&id=X&tags=Y''. Visit <a href="http://www.flickr.com/services/feeds/docs/photos_public/">http://www.flickr.com/services/feeds/docs/photos_public/</a> for documentation.', '*', 'required', 0),
('flickr', 'size', 'The size of the flickr images that are retrieved.', 'square|thumbnail|small|medium', NULL, 0),
('image', 'detail', 'Set the level of detail on the image.', 'image|filename', NULL, 0),
('image', 'maxheight', 'Set the maximum height in pixels of the image, leave blank for no maximum', '*', 'integer', 0),
('image', 'maxwidth', 'Set the maximum width in pixels of the image, leave blank for no maximum', '*', 'integer', 0),
('image', 'page', 'Select the page to grab image from.', 'current|*', NULL, 0),
('image', 'stock', 'Default to stock image if available.', 'yes|no', NULL, 0),
('menu', 'back_button', 'Include a back button on the menu if applicable.', 'no|yes', NULL, 0),
('menu', 'detail', 'Set the style of the menu.', 'adxmenu|horizontal|vertical', NULL, 0),
('menu', 'items', 'Select if menu shows neighbour''s pages or child''s pages.', 'neighbours|children', NULL, 0),
('menu', 'limit', 'Set how many levels the menu may descend.', '*', 'integer', 0),
('menu', 'menu', 'Set a specific menu to limit matches to. Leave blank to not impose this limit.', '*', 'alpha_numeric', 0),
('menu', 'page', 'Select the page to draw pages from. ''Current'' shows only neighbours to this page, or you can select your own page.', 'current|*', NULL, 0),
('posts', 'category', 'Limit posts to a specific category.', 'all|none|*', NULL, 0),
('posts', 'detail', 'Set the level of detail on the posts, if you select ''template'', the template set for that post will be used.', 'small_listing|listing|preview|full|featured|template', NULL, 0),
('posts', 'featured', 'Show only posts that are featured, aren''t featured or all posts.', 'yes|no|only', NULL, 0),
('posts', 'limit', 'Set the amount of posts to show at once.', '*', 'integer', 0),
('posts', 'page', 'Select the page to draw posts from. ''Current'' shows only posts on this page, ''No page'' shows user created posts, or you can select your own page.', 'all|current|nopage|*', NULL, 1),
('posts', 'page_children', 'Include posts from all children of the specified page.', 'no|yes', NULL, 0),
('posts', 'sort', 'Sort post by their title, creation date, modified date, or their popularity.', 'creation-date|modified-date|alphabetical|popularity', NULL, 0),
('posts', 'user', 'Select a specific user to draw posts from. Setting this value overrides the page value.', '*', NULL, 1),
('scrape', 'limit', 'The number of items to pull.', '*', 'integer|required', 0),
('scrape', 'page', 'Select the page to draw items from.', '*', 'required', 0),
('scrape', 'start', 'The number of items to skip before pulling.', '*', 'integer|required', 0),
('scrape', 'tag', 'HTML tag to set as the items we are pulling from a page. Default is ''p'', the paragraph tag.', '*', 'alpha_numeric', 0),
('twitter', 'limit', 'Set the amount of tweets to show at once.', '*', 'integer', 0),
('twitter', 'query', 'This is the query string of the twitter feed, which should follow a format like ''&from=X&tag=Y''.', '*', 'required', 0),
('wall', 'limit', 'Set the amount of wall posts to show at once.', '*', 'integer', 0),
('wrapper', 'content', 'The content to be wrapped. This can include other blocks.', '+', 'required', 0),
('wrapper', 'on_categorization', 'Content visible when a post category is selected.', 'yes|no|only', NULL, 0),
('wrapper', 'on_pagination', 'Content visible when pagination is selected.', 'yes|no|only', NULL, 0),
('wrapper', 'on_signedon', 'Content visible when user is signed on.', 'yes|no|only', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `categoryid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `urlname` varchar(60) NOT NULL,
  `heldback` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(39) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) DEFAULT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ci_sessions`
--

INSERT INTO `ci_sessions` (`session_id`, `ip_address`, `user_agent`, `last_activity`, `user_data`) VALUES
('c51c3f8cb1c5027e27c2c330577d9ca6', '0.0.0.0', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1', 1346819255, 'a:2:{s:24:"flash:old:signon_message";b:0;s:24:"flash:new:signon_message";b:0;}');

-- --------------------------------------------------------

--
-- Table structure for table `crons`
--

CREATE TABLE IF NOT EXISTS `crons` (
  `cron` varchar(50) NOT NULL,
  `last_run` datetime NOT NULL,
  PRIMARY KEY (`cron`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `crons`
--

INSERT INTO `crons` (`cron`, `last_run`) VALUES
('count_views', '2012-09-04 04:00:13'),
('site_backup', '2012-09-04 04:02:05');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `fileid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `filename` varchar(200) NOT NULL,
  `extension` varchar(5) DEFAULT NULL,
  `isimage` int(1) unsigned NOT NULL DEFAULT '0',
  `listed` int(1) unsigned NOT NULL,
  `downloadcount` int(10) unsigned NOT NULL DEFAULT '0',
  `attachedto` varchar(5) NOT NULL DEFAULT 'site',
  `attachedid` int(10) unsigned DEFAULT NULL,
  `css` int(1) unsigned NOT NULL,
  `js` int(1) unsigned NOT NULL,
  `filetypeid` int(10) unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `datemodified` datetime NOT NULL,
  PRIMARY KEY (`fileid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `files_stockimages`
--

CREATE TABLE IF NOT EXISTS `files_stockimages` (
  `fileid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fileid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `form` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` varchar(39) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`ip`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `urlname` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `public` int(11) NOT NULL,
  PRIMARY KEY (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `version` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`version`) VALUES
(8);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `adminid` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `action` varchar(20) NOT NULL,
  `actionon` varchar(20) NOT NULL,
  `actiononid` int(10) unsigned NOT NULL,
  `parentid` int(10) unsigned NOT NULL,
  `scope` varchar(20) DEFAULT NULL,
  `scopeid` int(10) unsigned DEFAULT NULL,
  `content` text,
  `note` text NOT NULL,
  PRIMARY KEY (`date`,`action`,`actionon`,`actiononid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `orderid` varchar(50) NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` varchar(200) NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `postalcode` varchar(30) NOT NULL,
  `country` varchar(50) NOT NULL,
  `phone` varchar(16) NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `date` datetime NOT NULL,
  `completed` int(1) NOT NULL DEFAULT '0',
  `paypal_transaction` varchar(30) DEFAULT NULL,
  `reviewed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orders_items`
--

CREATE TABLE IF NOT EXISTS `orders_items` (
  `orderid` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` float NOT NULL,
  `tax` float NOT NULL,
  PRIMARY KEY (`orderid`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `pageid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu` varchar(10) NOT NULL DEFAULT 'none',
  `title` varchar(100) DEFAULT NULL,
  `content` text,
  `pageof` int(10) unsigned DEFAULT NULL,
  `imageid` int(10) unsigned DEFAULT NULL,
  `datemodified` datetime NOT NULL,
  `link` varchar(150) DEFAULT NULL,
  `published` int(1) NOT NULL DEFAULT '0',
  `protected` int(1) NOT NULL DEFAULT '0',
  `position` int(10) unsigned DEFAULT NULL,
  `urlname` varchar(200) DEFAULT NULL,
  `needsubscription` int(1) NOT NULL DEFAULT '0',
  `pagepostname` int(1) NOT NULL DEFAULT '0',
  `page_templateid` int(10) unsigned DEFAULT NULL,
  `post_templateid` int(10) unsigned DEFAULT NULL,
  `rss_blockid` int(10) unsigned DEFAULT NULL,
  `pagination_blockid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`pageid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=523424 ;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`pageid`, `menu`, `title`, `content`, `pageof`, `imageid`, `datemodified`, `link`, `published`, `protected`, `position`, `urlname`, `needsubscription`, `pagepostname`, `page_templateid`, `post_templateid`, `rss_blockid`, `pagination_blockid`) VALUES
(1, 'main', 'Search', NULL, NULL, NULL, '2010-08-26 14:54:57', '/search', 1, 0, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
(2, 'main', 'Profile', NULL, NULL, NULL, '2010-08-26 13:44:18', '/profile', 1, 1, 2, NULL, 0, 0, NULL, NULL, NULL, NULL),
(3, 'main', 'Account', NULL, NULL, NULL, '2010-08-26 13:43:40', '/account', 1, 1, 3, NULL, 0, 0, NULL, NULL, NULL, NULL),
(4, 'main', 'Sign off', NULL, 3, NULL, '2010-10-10 11:14:18', '/signoff', 1, 0, 6, NULL, 0, 0, NULL, NULL, NULL, NULL),
(5, 'main', 'Manage security', NULL, 3, NULL, '2010-08-26 13:38:58', '/manage_security', 1, 1, 1, NULL, 0, 0, NULL, NULL, NULL, NULL),
(6, 'main', 'Manage users', NULL, 3, NULL, '2010-08-26 13:40:36', '/manage_users', 1, 1, 2, NULL, 0, 0, NULL, NULL, NULL, NULL),
(7, 'main', 'Manage pages', NULL, 3, NULL, '2010-08-26 13:42:06', '/manage_pages', 1, 1, 3, NULL, 0, 0, NULL, NULL, NULL, NULL),
(8, 'main', 'Manage content', NULL, 3, NULL, '2010-08-26 13:42:28', '/manage_content', 1, 1, 4, NULL, 0, 0, NULL, NULL, NULL, NULL),
(9, 'main', 'Manage activity', NULL, 3, NULL, '2010-08-26 13:42:52', '/manage_activity', 1, 1, 5, NULL, 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages_menus`
--

CREATE TABLE IF NOT EXISTS `pages_menus` (
  `menu` varchar(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`menu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages_menus`
--

INSERT INTO `pages_menus` (`menu`, `name`) VALUES
('main', 'Main menu'),
('nomenu', 'Under no menu');

-- --------------------------------------------------------

--
-- Table structure for table `pages_protection`
--

CREATE TABLE IF NOT EXISTS `pages_protection` (
  `pageid` int(10) unsigned NOT NULL,
  `roleid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`pageid`,`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pingbacks`
--

CREATE TABLE IF NOT EXISTS `pingbacks` (
  `pingbackid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(10) unsigned NOT NULL DEFAULT '0',
  `source` varchar(200) NOT NULL,
  `title` varchar(100) NOT NULL,
  `summary` text NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(39) NOT NULL,
  `featured` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pingbackid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `placeholders`
--

CREATE TABLE IF NOT EXISTS `placeholders` (
  `attachedto` varchar(4) NOT NULL,
  `oldname` varchar(200) NOT NULL,
  `newname` varchar(200) NOT NULL,
  `date` datetime NOT NULL,
  `redirect` int(1) unsigned NOT NULL,
  PRIMARY KEY (`attachedto`,`oldname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `postid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned DEFAULT NULL,
  `pageid` int(10) unsigned DEFAULT NULL,
  `views` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text CHARACTER SET utf8,
  `css` text,
  `javascript` text,
  `date` datetime NOT NULL,
  `datemodified` datetime NOT NULL,
  `featured` int(1) NOT NULL DEFAULT '0',
  `published` int(1) unsigned NOT NULL DEFAULT '0',
  `reviewed` int(1) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `urlname` varchar(200) NOT NULL,
  `imageid` int(10) unsigned DEFAULT NULL,
  `originalurl` varchar(250) DEFAULT NULL,
  `needsubscription` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`postid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts_categories`
--

CREATE TABLE IF NOT EXISTS `posts_categories` (
  `categoryid` int(11) NOT NULL,
  `postid` int(11) NOT NULL,
  PRIMARY KEY (`categoryid`,`postid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_comments`
--

CREATE TABLE IF NOT EXISTS `posts_comments` (
  `commentid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned DEFAULT NULL,
  `displayname` varchar(30) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `ip` varchar(39) DEFAULT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL,
  `featured` int(1) NOT NULL,
  `reviewed` int(1) NOT NULL,
  `new` int(1) NOT NULL,
  PRIMARY KEY (`commentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts_comments_banned`
--

CREATE TABLE IF NOT EXISTS `posts_comments_banned` (
  `ip` varchar(39) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_contributors`
--

CREATE TABLE IF NOT EXISTS `posts_contributors` (
  `postid` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`postid`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_events`
--

CREATE TABLE IF NOT EXISTS `posts_events` (
  `postid` int(10) unsigned NOT NULL DEFAULT '0',
  `location` varchar(50) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  PRIMARY KEY (`postid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_references`
--

CREATE TABLE IF NOT EXISTS `posts_references` (
  `postid` int(10) unsigned NOT NULL,
  `referenceid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`postid`,`referenceid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `posts_theme_files`
--

CREATE TABLE IF NOT EXISTS `posts_theme_files` (
  `postid` int(10) unsigned NOT NULL,
  `file` varchar(250) NOT NULL,
  `type` varchar(3) NOT NULL,
  PRIMARY KEY (`postid`,`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quotas`
--

CREATE TABLE IF NOT EXISTS `quotas` (
  `quotaid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `other_roles_allowed` int(1) unsigned NOT NULL,
  PRIMARY KEY (`quotaid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quotas_defaults`
--

CREATE TABLE IF NOT EXISTS `quotas_defaults` (
  `quotaid` int(10) unsigned NOT NULL,
  `templateid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`quotaid`,`templateid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `quotas_filetypes`
--

CREATE TABLE IF NOT EXISTS `quotas_filetypes` (
  `filetypeid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quotaid` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `cap` varchar(3) NOT NULL,
  PRIMARY KEY (`filetypeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quotas_protection`
--

CREATE TABLE IF NOT EXISTS `quotas_protection` (
  `quotaid` int(10) unsigned NOT NULL,
  `roleid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`quotaid`,`roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE IF NOT EXISTS `templates` (
  `templateid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `type` varchar(5) NOT NULL,
  `pageid` int(10) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `simple` int(1) NOT NULL,
  `pagepostname` int(1) unsigned NOT NULL,
  PRIMARY KEY (`templateid`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `templates_defaults`
--

CREATE TABLE IF NOT EXISTS `templates_defaults` (
  `templateid` int(10) unsigned NOT NULL,
  `pageid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`templateid`,`pageid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `templates_fields`
--

CREATE TABLE IF NOT EXISTS `templates_fields` (
  `templateid` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `htmlcode` varchar(20) NOT NULL,
  `form_type` int(1) unsigned NOT NULL,
  `required` int(1) unsigned NOT NULL,
  PRIMARY KEY (`templateid`,`htmlcode`),
  UNIQUE KEY `templateid` (`templateid`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `templates_fields_values`
--

CREATE TABLE IF NOT EXISTS `templates_fields_values` (
  `templateid` int(10) unsigned NOT NULL,
  `htmlcode` varchar(10) NOT NULL,
  `attachedto` varchar(5) NOT NULL,
  `attachedid` int(10) unsigned NOT NULL,
  `value` varchar(9999) NOT NULL,
  PRIMARY KEY (`templateid`,`htmlcode`,`attachedto`,`attachedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `displayname` varchar(30) NOT NULL,
  `urlname` varchar(30) NOT NULL,
  `code` varchar(50) NOT NULL DEFAULT '',
  `registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastsignon` datetime NOT NULL,
  `datemodified` datetime NOT NULL,
  `statusid` int(1) NOT NULL DEFAULT '0',
  `mailinglist` int(1) unsigned NOT NULL DEFAULT '1',
  `mailinglist_code` varchar(50) NOT NULL,
  `getmessages` int(1) unsigned NOT NULL DEFAULT '1',
  `profile` text,
  `profilepicture` int(10) unsigned DEFAULT NULL,
  `facebook_uid` varchar(20) DEFAULT NULL,
  `twitter` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `facebook_uid` (`facebook_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_blocked`
--

CREATE TABLE IF NOT EXISTS `users_blocked` (
  `userid` int(10) unsigned NOT NULL,
  `blockedid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userid`,`blockedid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_blogs`
--

CREATE TABLE IF NOT EXISTS `users_blogs` (
  `userid` int(10) unsigned NOT NULL,
  `rssfeed` varchar(150) NOT NULL,
  PRIMARY KEY (`userid`,`rssfeed`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `userid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userid`,`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_status`
--

CREATE TABLE IF NOT EXISTS `users_status` (
  `statusid` int(1) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`statusid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_status`
--

INSERT INTO `users_status` (`statusid`, `status`) VALUES
(1, 'Featured'),
(0, 'Normal'),
(-1, 'Moderated'),
(-2, 'Banned');

-- --------------------------------------------------------

--
-- Table structure for table `users_subscriptions`
--

CREATE TABLE IF NOT EXISTS `users_subscriptions` (
  `userid` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `typeid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_subscriptions_types`
--

CREATE TABLE IF NOT EXISTS `users_subscriptions_types` (
  `typeid` int(10) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `price` float NOT NULL,
  `tax` float NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_subscriptions_types`
--

INSERT INTO `users_subscriptions_types` (`typeid`, `type`, `price`, `tax`) VALUES
(1, 'test', 1, 0.15);

-- --------------------------------------------------------

--
-- Table structure for table `users_subscriptions_views`
--

CREATE TABLE IF NOT EXISTS `users_subscriptions_views` (
  `ip` varchar(15) NOT NULL,
  `postid` int(10) unsigned NOT NULL,
  `touched` datetime NOT NULL,
  PRIMARY KEY (`ip`,`postid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `variables`
--

CREATE TABLE IF NOT EXISTS `variables` (
  `variable_key` varchar(50) NOT NULL,
  `variable_value` varchar(1000) NOT NULL,
  PRIMARY KEY (`variable_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE IF NOT EXISTS `views` (
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL,
  `week` date NOT NULL,
  `touched` datetime NOT NULL,
  `type` varchar(4) NOT NULL,
  `typeid` int(10) unsigned NOT NULL,
  `hits` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userid`,`ip`,`week`,`type`,`typeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `walls`
--

CREATE TABLE IF NOT EXISTS `walls` (
  `content` varchar(140) NOT NULL,
  `name` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `ip` varchar(39) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`ip`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
