<?php foreach( $items as $item) : ?>
<item>
	<title><?php echo $item['title']; ?></title>
	<link><?php echo $item['link']; ?></link>
	<description><![CDATA[<?php echo $item['description']; ?>]]></description>
	<pubDate><?php echo $item['modified']; ?></pubDate>
</item>
<?php endforeach; ?>