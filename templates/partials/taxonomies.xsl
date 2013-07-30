<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="taxonomies">
		<xsl:apply-templates select="taxonomy" />
	</xsl:template>


	<xsl:template match="taxonomy">
		<xsl:if test="count(terms/term) > 0">
			<dl class="taxonomy taxonomy-{name}">
				<dt><xsl:value-of select="label" disable-output-escaping="yes" /></dt>
				<xsl:apply-templates select="terms" />
			</dl>
		</xsl:if>
	</xsl:template>


	<xsl:template match="terms">
		<xsl:apply-templates select="term" />
	</xsl:template>


	<xsl:template match="term">
		<dd><a href="{permalink}"><xsl:value-of select="name" disable-output-escaping="yes" /></a></dd>
	</xsl:template>
	
</xsl:stylesheet>