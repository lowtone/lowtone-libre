<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2013, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<!-- Breadcrumbs -->

	<xsl:template match="breadcrumbs">
		<ul class="breadcrumbs">
			<xsl:apply-templates select="trail/crumb" />
		</ul>
	</xsl:template>


	<!-- Single crumb -->

	<xsl:template match="crumb">
		<li>
			<a>
				<xsl:attribute name="href">
					<xsl:value-of select="uri" />
				</xsl:attribute>
				<xsl:attribute name="class">
					<xsl:if test="position()=1">
						<xsl:text>first </xsl:text>
					</xsl:if>
					<xsl:if test="position()=last()">
						<xsl:text>last </xsl:text>
					</xsl:if>
					<xsl:value-of select="class" />
				</xsl:attribute>
				<xsl:value-of select="title" />
			</a>
		</li>
	</xsl:template>
	
</xsl:stylesheet>