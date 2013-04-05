<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<!-- Custom fields -->
	
	<xsl:template match="custom_fields">
		<xsl:variable name="publicMeta" select="meta[@public='1']" />
		<xsl:variable name="hasMeta" select="boolean(count($publicMeta))" />
		
		<div>
			<xsl:attribute name="class">
				<xsl:text>custom-fields</xsl:text>
				<xsl:if test="not($hasMeta)">
					<xsl:text> empty</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<header>
				<h1><xsl:value-of select="locales/title" /></h1>
			</header>
			<xsl:choose>
				<xsl:when test="$hasMeta">
					<dl>
						<xsl:apply-templates select="$publicMeta" />
					</dl>
				</xsl:when>
				<xsl:otherwise>
					<p class="no-items"><xsl:value-of select="locales/no_meta" /></p>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	

	<!-- Meta -->
	
	<xsl:template match="meta">
		<dt><xsl:value-of select="key" /></dt>
		<dd><xsl:value-of select="value" disable-output-escaping="yes" /></dd>
	</xsl:template>

</xsl:stylesheet>