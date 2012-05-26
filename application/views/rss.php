<rss version="2.0">
<channel>

<title><?php echo $title; ?></title>
<link><?php echo $link; ?></link>
<description><![CDATA[<?php echo $description; ?>]]></description>
<lastBuildDate><?php echo $lastBuildDate; ?></lastBuildDate>
<language><?php echo $language; ?></language>

<image>
	<title><?php echo $title; ?></title>
	<url><?php echo $image_url; ?></url>
	<link><?php echo $link; ?></link>
	<width>16</width>
	<height>16</height>
</image>

<?php echo $items; ?>

</channel>
</rss> 
