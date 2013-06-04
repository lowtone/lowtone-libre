<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<!-- Single menu -->
	
	<xsl:template match="menu">
		<xsl:param name="width" select="false()" />

		<xsl:variable name="hasItems" select="boolean(count(items/item))" />

		<nav id="{@id}">
			<xsl:attribute name="class">
				<xsl:text>menu</xsl:text>
				<xsl:if test="boolean($width)">
					<xsl:text> </xsl:text><xsl:value-of select="$width" /><xsl:text> column</xsl:text>
				</xsl:if>
				<xsl:if test="not($hasItems)">
					<xsl:text> empty</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<xsl:apply-templates select="items" />
		</nav>
	</xsl:template>
	
	
	<!-- Items -->
	
	<xsl:template match="items">
		<ul class="depth-{@depth}">
			<xsl:apply-templates select="item" />
		</ul>
	</xsl:template>
	
	
	<!-- Item -->
	
	<xsl:template match="item">
		<li>
			<xsl:attribute name="class">
				<xsl:for-each select="classes/class">
					<xsl:value-of select="." /><xsl:text> </xsl:text>
				</xsl:for-each>
			</xsl:attribute>
			<a href="{url}" title="{attr_title}">
				<span class="title"><xsl:value-of select="title" /></span>
				<xsl:if test="string(description)">
					<span class="description"><xsl:value-of select="description" /></span>
				</xsl:if>
			</a>
			<xsl:apply-templates select="items" />
		</li>
	</xsl:template>
	
</xsl:stylesheet>