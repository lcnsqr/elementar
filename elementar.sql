-- phpMyAdmin SQL Dump
-- version 3.4.4
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: 15/09/2011 às 06h36min
-- Versão do Servidor: 5.5.14
-- Versão do PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `elementar`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `content`
--

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
(1, 'Home', 'home', 1, 1, 'published', '2011-08-29 16:19:59', '2011-09-12 07:05:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_field`
--

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
(7, 1, 1, '<p>Welcome to the machine</p>');

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_parent`
--

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
(1, 1, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_type`
--

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
(1, 'Home', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `content_type_field`
--

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
(1, 1, 'Corpo', 'corpo', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `element`
--

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

-- --------------------------------------------------------

--
-- Estrutura da tabela `element_field`
--

CREATE TABLE IF NOT EXISTS `element_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `element_type_field_id` int(10) unsigned DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `element_parent`
--

CREATE TABLE IF NOT EXISTS `element_parent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `element_type`
--

CREATE TABLE IF NOT EXISTS `element_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `element_type_field`
--

CREATE TABLE IF NOT EXISTS `element_type_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element_type_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sname` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `field_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `field_type`
--

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
(5, 'Destino', 'target', 'URI de destino no site'),
(6, 'Textarea', 'textarea', 'Text/code snippet'),
(7, 'Menu', 'menu', 'Lista de itens de menu');

-- --------------------------------------------------------

--
-- Estrutura da tabela `html_meta`
--

CREATE TABLE IF NOT EXISTS `html_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alt` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `uri` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `uri_thumb` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `template`
--

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
(1, '', '<h1>{name}</h1>\n\n{if corpo != ''''}\n{corpo}\n{/if}\n', 'body {\nfont-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;\nfont-size: 12px;\nbackground-color: #fff;\ncolor: #2d2d2d;\n}\n\n#menu_topo  {\nheight: 20px;\n}\n#menu_topo ul {\nmargin: 0;\npadding: 0;\n}\n#menu_topo li {\nfloat: left;\nlist-style-position: inside;\nlist-style-type: disc;\n}\n#menu_topo li:first-child {\nlist-style-position: outside;\nlist-style-type: none;\n}\n#menu_topo a {\nmargin-right: .9em;\n}\n#menu_topo a.current {\nfont-weight: bold;\n}\n#menu_topo * ul {\ndisplay: none;\n}', '', '<script type="text/javascript" src="/js/jquery-1.6.2.min.js"></script>', '2011-08-19 09:28:59', '2011-09-15 09:20:35');

-- --------------------------------------------------------

--
-- Estrutura da tabela `upload_session`
--

CREATE TABLE IF NOT EXISTS `upload_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `error` tinyint(1) NOT NULL DEFAULT '0',
  `uri` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `image_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
