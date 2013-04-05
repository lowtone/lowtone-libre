<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<!-- Single attachment -->
	
	<xsl:template match="attachment|thumbnail">
		<xsl:param name="size" />
		<xsl:param name="link" />

		<xsl:variable name="url">
			<xsl:choose>
				<xsl:when test="boolean($size)">
					<xsl:value-of select="attachment_metadata/sizes/*[local-name()=$size]/url" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="attachment_metadata/url" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<figure>
			<xsl:attribute name="class">
				<xsl:text>attachment</xsl:text>
				<xsl:if test="local-name()!='attachment'">
					<xsl:text> </xsl:text><xsl:value-of select="local-name()" />
				</xsl:if>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="$link">
					<a href="{$link}"><img src="{$url}" alt="" itemprop="image" /></a>
				</xsl:when>
				<xsl:otherwise>
					<img src="{$url}" alt="" />
				</xsl:otherwise>
			</xsl:choose>
			<figcaption><xsl:value-of select="title" /></figcaption>
		</figure>
	</xsl:template>

</xsl:stylesheet>