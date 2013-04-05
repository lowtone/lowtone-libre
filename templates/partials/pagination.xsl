<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	
	<!-- Pagination -->
	
	<xsl:template match="pagination">
		<ul>
			<xsl:attribute name="class">
				<xsl:text>pagination</xsl:text>
				<xsl:if test="first">
					<xsl:text> first</xsl:text>
				</xsl:if>
				<xsl:if test="last">
					<xsl:text> last</xsl:text>
				</xsl:if>
				<xsl:if test="previous">
					<xsl:text> previous</xsl:text>
				</xsl:if>
				<xsl:if test="next">
					<xsl:text> next</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<xsl:apply-templates select="first" />
			<xsl:apply-templates select="previous" />
			<xsl:apply-templates select="pages" />
			<xsl:apply-templates select="next" />
			<xsl:apply-templates select="last" />
		</ul>
	</xsl:template>
	
	
	<!-- Text labels -->
	
	<xsl:template match="first|previous|next|last">
		<xsl:apply-templates select="page">
			<xsl:with-param name="label" select="locales/title" />
			<xsl:with-param name="name" select="name()" />
		</xsl:apply-templates>
	</xsl:template>
	
	
	<!-- Pages -->
	
	<xsl:template match="pages">
		<ul class="pages">
			<xsl:apply-templates select="page" />
		</ul>
	</xsl:template>
	
	
	<!-- Page -->
	
	<xsl:template match="page">
		<xsl:param name="label" select="number" />
		<xsl:param name="name" />
		
		<li>
			<xsl:attribute name="class">
				<xsl:text>page page-</xsl:text><xsl:value-of select="number" />
				<xsl:if test="$name">
					<xsl:text> </xsl:text><xsl:value-of select="$name" />
				</xsl:if>
				<xsl:if test="@current='1'">
					<xsl:text> current</xsl:text>
				</xsl:if>
			</xsl:attribute>
			<a href="{url}"><xsl:value-of select="$label" /></a>
		</li>
	</xsl:template>

</xsl:stylesheet>