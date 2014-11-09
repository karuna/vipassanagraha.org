<!--
/** 
 * @version		3.0.6 plugins/j2xml/importer15/1506.xsl
 * 
 * @package		J2XML
 * @subpackage	plg_j2xml_importer15
 * @since		2.5
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2013 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License 
 * or other free or open source software licenses.
 */
-->
<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xs="http://www.w3.org/2001/XMLSchema">
<xsl:output 
	cdata-section-elements="title alias introtext fulltext attribs metadata name"
	encoding="UTF-8"
	indent="yes"
	/>
 
<xsl:template match="/*[name() != 'j2xml']">
<xsl:copy-of select="."/>
</xsl:template>

<xsl:template match="/j2xml[@version != '1.5.6']">
<xsl:copy-of select="."/>
</xsl:template>

<xsl:template match="/j2xml[@version = '1.5.6' and count(/j2xml/content) &gt; 0]">
<j2xml version="12.5.0">
	<xsl:apply-templates select="/j2xml/content" />
	<xsl:apply-templates select="/j2xml/section" />
	<xsl:apply-templates select="/j2xml/category" />
	<xsl:apply-templates select="/j2xml/user" />
	<xsl:apply-templates select="/j2xml/img" />
</j2xml>
</xsl:template>

<xsl:template match="/j2xml[@version = '1.5.6' and count(/j2xml/content) = 0]">
<j2xml version="12.5.0">
	<xsl:apply-templates select="/j2xml/section" />
	<xsl:apply-templates select="/j2xml/category" />
	<xsl:apply-templates select="/j2xml/user" />
	<xsl:apply-templates select="/j2xml/img" />
</j2xml>
</xsl:template>

<!-- RECURSIVE TEMPLATE, KEEPS CALLING ITSELF UNTIL ALL ITEMS ARE PROCESSED -->
<xsl:template name="toJson">
<xsl:param name="ini"></xsl:param><xsl:variable name="first"><xsl:choose><xsl:when test="contains($ini,'&#10;')"><xsl:copy-of select="substring-before($ini,'&#10;')" /></xsl:when><xsl:otherwise><xsl:copy-of select="$ini" /></xsl:otherwise></xsl:choose></xsl:variable><xsl:variable name="remaining" select="substring-after($ini,'&#10;')" /><xsl:choose><xsl:when test="substring-before($first,'=') = 'readmore'">"alternative_readmore"</xsl:when><xsl:otherwise>"<xsl:copy-of select="substring-before($first,'=')"/>"</xsl:otherwise></xsl:choose>:"<xsl:copy-of select="substring-after($first,'=')"/>"<xsl:if test="$remaining">,<xsl:call-template name="toJson"><xsl:with-param name="ini" select="$remaining"></xsl:with-param></xsl:call-template></xsl:if>
</xsl:template>

<xsl:template name ="max">
	<xsl:param name ="list" />
	<xsl:choose>
		<xsl:when test ="$list">
			<xsl:variable name ="first" select ="$list[1]" />
			<xsl:variable name ="rest">
				<xsl:call-template name ="max">
					<xsl:with-param name ="list" select ="$list[position() != 1]" />
				</xsl:call-template>
			</xsl:variable>
			<xsl:choose>
				<xsl:when test="$first &gt; $rest">
					<xsl:value-of select ="$first"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select ="$rest"/>     
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:otherwise>0</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="content">
<content>
	<id><xsl:value-of select="id"/></id>
	<title><xsl:value-of select="title"/></title>
	<xsl:variable name="alias" select="alias" />
	<xsl:variable name="sectionid" select="sectionid" />
	<xsl:variable name="catid" select="catid" />
	<alias><xsl:value-of select="alias"/><xsl:if test="count(/j2xml/content[alias=$alias and sectionid=$sectionid and catid=$catid]) > 1">-<xsl:value-of select="id"/></xsl:if></alias>
	<introtext><xsl:value-of select="introtext"/></introtext>
	<fulltext><xsl:value-of select="fulltext"/></fulltext>
	<state><xsl:choose>
			<xsl:when test="state = -1">2</xsl:when>
			<xsl:otherwise><xsl:value-of select="state"/></xsl:otherwise>
	</xsl:choose></state>
	<created><xsl:value-of select="created"/></created>
	<created_by_alias><xsl:value-of select="created_by_alias"/></created_by_alias>
	<modified><xsl:value-of select="modified"/></modified>
	<publish_up><xsl:value-of select="publish_up"/></publish_up>
	<publish_down><xsl:value-of select="publish_down"/></publish_down>
	<images><![CDATA[{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}]]></images>
	<urls><![CDATA[{"urla":null,"urlatext":"","targeta":"","urlb":null,"urlbtext":"","targetb":"","urlc":null,"urlctext":"","targetc":""}]]></urls>
	<attribs>{<xsl:call-template name="toJson"><xsl:with-param name="ini" select="attribs"></xsl:with-param></xsl:call-template>}</attribs>
	<version><xsl:value-of select="version"/></version>
	<ordering><xsl:value-of select="ordering"/></ordering>
	<metakey><xsl:value-of select="metakey"/></metakey>
	<metadesc><xsl:value-of select="metadesc"/></metadesc>
	<access><xsl:value-of select="access+1"/></access>
	<hits><xsl:value-of select="hits"/></hits>
	<metadata><![CDATA[{"robots":"","author":"","rights":"","xreference":""}]]></metadata>
	<language><![CDATA[*]]></language>
	<xreference></xreference>
	<catid>
		<xsl:choose>
			<xsl:when test="catid">
				<xsl:value-of select="sectionid"/>/<xsl:value-of select="catid"/>
			</xsl:when>
			<xsl:otherwise>uncategorised</xsl:otherwise>
		</xsl:choose>
	</catid>
	<created_by><xsl:value-of select="created_by"/></created_by>
	<modified_by><xsl:value-of select="modified_by"/></modified_by>
	<featured><xsl:value-of select="frontpage"/></featured>
	<rating_sum>0</rating_sum>
	<rating_count>0</rating_count>
</content>
</xsl:template>

<xsl:template match="category">
<category>
	<id><xsl:choose>
			<xsl:when test="count(/j2xml/content) &gt; 0">0</xsl:when>
			<xsl:otherwise><xsl:value-of select="id"/></xsl:otherwise>
	</xsl:choose></id>
	<path><xsl:value-of select="sectionid"/>/<xsl:value-of select="alias"/></path>
	<extension><![CDATA[com_content]]></extension>
	<title><xsl:value-of select="title"/></title>
	<alias><xsl:value-of select="alias"/></alias>
	<note></note>
	<description><xsl:value-of select="description"/></description>
	<published><xsl:value-of select="published"/></published>
	<access><xsl:value-of select="access+1"/></access>
	<params>{<xsl:call-template name="toJson"><xsl:with-param name="ini" select="params"></xsl:with-param></xsl:call-template>}</params>
	<metadesc></metadesc>
	<metakey></metakey>
	<metadata><![CDATA[{"author":"","robots":""}]]></metadata>
	<created_time></created_time>
	<modified_time></modified_time>
	<hits></hits>
	<language><![CDATA[*]]></language>
	<created_user_id></created_user_id>
	<modified_user_id></modified_user_id>
</category>
</xsl:template>

<xsl:template match="section">
<category>
	<!-- Get the maximum value-->
	<xsl:variable name ="maxcatid">
		<xsl:call-template name ="max">
			<xsl:with-param name ="list" select ="/j2xml/category/id" />
		</xsl:call-template>
	</xsl:variable>
	<id>
		<xsl:choose>
			<xsl:when test="count(/j2xml/content) &gt; 0">
				0
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="id + $maxcatid"/>     
			</xsl:otherwise>
		</xsl:choose>	
	</id>
	<path><xsl:value-of select="alias"/></path>
	<extension><![CDATA[com_content]]></extension>
	<title><xsl:value-of select="title"/></title>
	<alias><xsl:value-of select="alias"/></alias>
	<note></note>
	<description><xsl:value-of select="description"/></description>
	<published><xsl:value-of select="published"/></published>
	<access><xsl:value-of select="access+1"/></access>
	<params>{<xsl:call-template name="toJson"><xsl:with-param name="ini" select="params"></xsl:with-param></xsl:call-template>}</params>
	<metadesc></metadesc>
	<metakey></metakey>
	<metadata><![CDATA[{"author":"","robots":""}]]></metadata>
	<created_time></created_time>
	<modified_time></modified_time>
	<hits></hits>
	<language><![CDATA[*]]></language>
	<created_user_id></created_user_id>
	<modified_user_id></modified_user_id>
</category>
</xsl:template>

<xsl:template match="user">
<user>
	<id><xsl:value-of select="id"/></id>
	<name><xsl:value-of select="name"/></name>
	<username><xsl:value-of select="username"/></username>
	<email><xsl:value-of select="email"/></email>
	<password><xsl:value-of select="password"/></password>
	<usertype><![CDATA[deprecated]]></usertype>
	<block><xsl:value-of select="block"/></block>
	<sendEmail><xsl:value-of select="sendEmail"/></sendEmail>
	<registerDate><xsl:value-of select="registerDate"/></registerDate>
	<lastvisitDate><xsl:value-of select="lastvisitDate"/></lastvisitDate>
	<activation><xsl:value-of select="activation"/></activation>
	<params>{<xsl:call-template name="toJson"><xsl:with-param name="ini" select="params"></xsl:with-param></xsl:call-template>}</params>
	<lastResetTime><![CDATA[0000-00-00 00:00:00]]></lastResetTime>
	<resetCount>0</resetCount>
	<group><xsl:choose>
		<xsl:when test="usertype = 'Administrator' or usertype = 'Author' or usertype = 'Editor' or usertype = 'Manager' or usertype = 'Publisher'"><xsl:value-of select="usertype"/></xsl:when>
		<xsl:when test="usertype = 'Super Administrator'">Super Users</xsl:when>
		<xsl:otherwise>Registered</xsl:otherwise>
	</xsl:choose></group>
</user>
</xsl:template>

<xsl:template match="img">
	<xsl:copy-of select="."/>
</xsl:template>

</xsl:stylesheet>
