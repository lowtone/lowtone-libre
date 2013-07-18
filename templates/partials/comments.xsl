<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../../../../libs/lowtone/ui/forms/templates/form.xsl" />


	<!-- Combined comment output -->

	<xsl:template name="comments">
		<xsl:apply-templates select="comments" />
		<xsl:apply-templates select="comment_form" />
	</xsl:template>

	<!-- Comments -->
	
	<xsl:template match="comments">
		<xsl:param name="status" />

		<xsl:variable name="hasComments" select="boolean(count(comment))" />
		
		<div>
			<xsl:attribute name="class">
				<xsl:text>comments</xsl:text>
				<xsl:text> </xsl:text><xsl:value-of select="../comment_status" />
				<xsl:if test="not($hasComments)">
					<xsl:text> empty</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<header>
				<h1><xsl:value-of select="locales/title" /></h1>
			</header>
			<xsl:choose>
				<xsl:when test="$hasComments">
					<xsl:apply-templates select="comment" />
				</xsl:when>
				<xsl:otherwise>
					<p class="no-items"><xsl:value-of select="locales/no_comments" /></p>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	
	
	<!-- Single comment -->
	
	<xsl:template match="comment">
		<div class="comment">
			<header>
				<h1><xsl:value-of select="author" /></h1>
				<dl>
					<dt><xsl:value-of select="locales/date" /></dt>
					<dd><xsl:value-of select="date" /></dd>
				</dl>
			</header>
			<div class="content">
				<xsl:value-of select="content" disable-output-escaping="yes" />
			</div>
		</div>
	</xsl:template>


	<!-- Form -->

	<xsl:template match="comment_form">
		<div class="comment">
			<header>
				<h1><xsl:value-of select="locales/title" /></h1>
			</header>
			<xsl:apply-templates select="form" />
		</div>
	</xsl:template>
	
</xsl:stylesheet>