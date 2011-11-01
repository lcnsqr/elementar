-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Servidor: mysql.atachado.com.br
-- Tempo de Geração: Out 31, 2011 as 10:31 PM
-- Versão do Servidor: 5.1.53
-- Versão do PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `lmntr`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `config`
--

INSERT INTO `config` (`id`, `name`, `value`) VALUES
(1, 'i18n', '[{"name":"Portugu\\u00eas","code":"por","default":true},{"name":"English","code":"eng","default":false}]');

-- --------------------------------------------------------

--
-- Estrutura da tabela `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE IF NOT EXISTS `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `content_type_id` int(10) unsigned NOT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `content`
--

INSERT INTO `content` (`id`, `name`, `sname`, `content_type_id`, `template_id`, `status`, `created`, `modified`) VALUES
(1, '{"por":"Casa","eng":"Home"}', 'casa', 1, 1, 'published', '2011-08-29 09:19:59', '2011-10-29 09:52:25');

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_field`
--

DROP TABLE IF EXISTS `content_field`;
CREATE TABLE IF NOT EXISTS `content_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `content_type_field_id` int(10) unsigned DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `content_field`
--

INSERT INTO `content_field` (`id`, `content_id`, `content_type_field_id`, `value`) VALUES
(7, 1, 1, '{"por":"<p>Bem-vindo &agrave; m&aacute;quina.<\\/p>","eng":"<p>Welcome to the machine.<\\/p>"}');

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_parent`
--

DROP TABLE IF EXISTS `content_parent`;
CREATE TABLE IF NOT EXISTS `content_parent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `content_parent`
--

INSERT INTO `content_parent` (`id`, `content_id`, `parent_id`) VALUES
(64, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_type`
--

DROP TABLE IF EXISTS `content_type`;
CREATE TABLE IF NOT EXISTS `content_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `template_id` int(10) unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `content_type`
--

INSERT INTO `content_type` (`id`, `name`, `template_id`) VALUES
(1, 'Home', 1),
(6, 'Padrão', 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_type_field`
--

DROP TABLE IF EXISTS `content_type_field`;
CREATE TABLE IF NOT EXISTS `content_type_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_type_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `field_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `content_type_field`
--

INSERT INTO `content_type_field` (`id`, `content_type_id`, `name`, `sname`, `field_type_id`) VALUES
(1, 1, 'Corpo', 'corpo', 3),
(6, 6, 'Corpo', 'corpo', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `element`
--

DROP TABLE IF EXISTS `element`;
CREATE TABLE IF NOT EXISTS `element` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `element_type_id` int(10) unsigned NOT NULL,
  `spread` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `element`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `element_field`
--

DROP TABLE IF EXISTS `element_field`;
CREATE TABLE IF NOT EXISTS `element_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `element_type_field_id` int(10) unsigned DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `element_field`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `element_parent`
--

DROP TABLE IF EXISTS `element_parent`;
CREATE TABLE IF NOT EXISTS `element_parent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `element_parent`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `element_type`
--

DROP TABLE IF EXISTS `element_type`;
CREATE TABLE IF NOT EXISTS `element_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `element_type`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `element_type_field`
--

DROP TABLE IF EXISTS `element_type_field`;
CREATE TABLE IF NOT EXISTS `element_type_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_type_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `field_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `element_type_field`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `field_type`
--

DROP TABLE IF EXISTS `field_type`;
CREATE TABLE IF NOT EXISTS `field_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `field_type`
--

INSERT INTO `field_type` (`id`, `name`, `sname`, `description`) VALUES
(1, 'Parágrafo', 'p', 'Parágrafo de hipertexto (texto, ligações)'),
(2, 'Imagem', 'img', 'Exibir uma imagem'),
(3, 'Hipertexto', 'hypertext', 'Conteúdo de hipertexto (texto, imagens, ligações, etc)'),
(4, 'Linha', 'line', 'Parágrafo curto sem formatação'),
(6, 'Destino', 'target', 'URI de destino no site'),
(7, 'Textarea', 'textarea', 'Text/code snippet'),
(8, 'Menu', 'menu', 'Lista de itens de menu'),
(9, 'Galeria de imagens', 'image_gallery', 'Galeria de imagens'),
(10, 'Galeria de vídeos', 'youtube_gallery', 'Galeria de vídeos YouTube');

-- --------------------------------------------------------

--
-- Estrutura da tabela `html_meta`
--

DROP TABLE IF EXISTS `html_meta`;
CREATE TABLE IF NOT EXISTS `html_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `html_meta`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `template`
--

DROP TABLE IF EXISTS `template`;
CREATE TABLE IF NOT EXISTS `template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `html` text COLLATE utf8_unicode_ci NOT NULL,
  `css` text COLLATE utf8_unicode_ci,
  `javascript` text COLLATE utf8_unicode_ci,
  `head` text COLLATE utf8_unicode_ci,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `template`
--

INSERT INTO `template` (`id`, `name`, `html`, `css`, `javascript`, `head`, `created`, `modified`) VALUES
(1, '', '<!--\n<div id="menu_topo">\n<ul>\n{menu-topo.links}\n<li>{link}</li>\n{/menu-topo.links}\n</ul>\n</div>\n-->\n\n<!--\n{navegacao}\n<p>{name}</p>\n{links}\n<p>{link}</p>\n{/links}\n{/navegacao}\n-->\n\n<h1>{name}</h1>\n\n<!--\n<div>\n{comandante-zapata.figura}\n<img src="{uri}" alt="{alt}" width="{width}" height="{height}" />\n{/comandante-zapata.figura}\n</div>\n-->\n\n{if corpo != ''''}\n{corpo}\n{/if}\n\n<hr />\n\n<!--\n{children}\n<p><a href="{uri}">{name}</a></p>\n{/children}\n-->', 'body {\nfont-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;\nfont-size: 12px;\nbackground-color: #fff;\ncolor: #2d2d2d;\n}\n\n#menu_topo  {\nheight: 20px;\n}\n#menu_topo ul {\nmargin: 0;\npadding: 0;\n}\n#menu_topo li {\nfloat: left;\nlist-style-position: inside;\nlist-style-type: disc;\n}\n#menu_topo li:first-child {\nlist-style-position: outside;\nlist-style-type: none;\n}\n#menu_topo a {\nmargin-right: .9em;\n}\n#menu_topo a.current {\nfont-weight: bold;\n}\n#menu_topo * ul {\ndisplay: none;\n}', '', '', '2011-08-19 02:28:59', '2011-10-31 22:21:36'),
(2, '', '<!--\n<div id="menu_topo">\n<ul>\n{menu-topo.links}\n<li>{link}</li>\n{/menu-topo.links}\n</ul>\n</div>\n-->\n\n{breadcrumb}\n<h1>{name}</h1>\n\n{corpo}\n\n<!--\n{adereco}\n<p>{name}</p>\n{figura}\n<p><img src="{uri}" alt="{alt}" width="{width}" height="{height}" /></p>\n{/figura}\n{/adereco}\n-->\n<p>All rights reserved</p>', '', '', '', '2011-09-15 10:13:35', '2011-10-31 22:28:41');

-- --------------------------------------------------------

--
-- Estrutura da tabela `upload_session`
--

DROP TABLE IF EXISTS `upload_session`;
CREATE TABLE IF NOT EXISTS `upload_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `uri` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `upload_session`
--

