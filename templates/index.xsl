<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="partials/menus.xsl" />
	<xsl:import href="partials/query.xsl" />
	<xsl:import href="partials/sidebars.xsl" />
	<xsl:import href="partials/breadcrumbs.xsl" />
	
	<xsl:output 
		method="html" 
		encoding="utf-8" 
		indent="no" 
		omit-xml-declaration="yes" />
		
		
	<!-- Main -->
	
	<xsl:template match="libre">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html>
			<xsl:apply-templates select="attributes/html" />
			<xsl:call-template name="html_head" />
			<xsl:call-template name="html_body" />
		</html>
	</xsl:template>

	<!-- HTML head -->

	<xsl:template name="html_head">
		<head>
			<xsl:value-of select="head" disable-output-escaping="yes" />
		</head>
	</xsl:template>


	<!-- HTML body -->

	<xsl:template name="html_body">
		<body>
			<xsl:apply-templates select="attributes/body" />
			<xsl:call-template name="header" />
			<xsl:call-template name="body" />
			<xsl:call-template name="footer" />
		</body>
	</xsl:template>


	<!-- Header -->

	<xsl:template name="header">
		<header id="header">
			<div class="container clearfix">
				<h1><xsl:value-of select="info/name" /></h1>
				<h4><xsl:value-of select="info/description" /></h4>
				<xsl:apply-templates select="menus/menu[@location='main']" />
			</div>
		</header>
	</xsl:template>


	<!-- Body -->

	<xsl:template name="body">
		<div id="body">
			<div class="container clearfix">
				<xsl:call-template name="main" />
				<xsl:call-template name="side" />
			</div>
		</div>
	</xsl:template>


	<!-- Main -->

	<xsl:template name="main">
		<xsl:param name="width">two-thirds</xsl:param>

		<section id="main" class="{$width} column">
			<xsl:call-template name="before_query" />
			<xsl:apply-templates select="query" />
			<xsl:call-template name="after_query" />
		</section>
	</xsl:template>


	<!-- Before query -->

	<xsl:template name="before_query" />


	<!-- After query -->

	<xsl:template name="after_query" />


	<!-- Side -->

	<xsl:template name="side">
		<xsl:param name="width">one-third</xsl:param>

		<xsl:choose>
			<xsl:when test="count(sidebars/sidebar[@id='front_page']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='front_page']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='home']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='home']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='search']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='search']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='author']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='author']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='page']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='page']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='tax']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='tax']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='category']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='category']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:when test="count(sidebars/sidebar[@id='single']/widgets/widget) &gt; 0">
				<xsl:apply-templates select="sidebars/sidebar[@id='single']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="sidebars/sidebar[@id='sidebar']">
					<xsl:with-param name="width" select="$width" />
				</xsl:apply-templates>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>


	<!-- Footer -->

	<xsl:template name="footer">
		<footer id="footer">
			<div class="container clearfix">
				<xsl:apply-templates select="sidebars/sidebar[@id='sidebar-2']" />
				<xsl:apply-templates select="sidebars/sidebar[@id='sidebar-3']" />
				<xsl:apply-templates select="sidebars/sidebar[@id='sidebar-4']" />
			</div>
		</footer>
		<xsl:value-of select="footer" disable-output-escaping="yes" />
	</xsl:template>


	<!-- Attributes -->

	<xsl:template match="attributes/*">
		<xsl:for-each select="*">
			<xsl:attribute name="{local-name()}">
				<xsl:value-of select="." />
			</xsl:attribute>
		</xsl:for-each>
	</xsl:template>


	<!-- Garbage -->

	<xsl:template select="garbage">
		<div class="garbage">
			<xsl:value-of select="." disable-output-escaping="yes" />
		</div>
	</xsl:template>
	
</xsl:stylesheet>