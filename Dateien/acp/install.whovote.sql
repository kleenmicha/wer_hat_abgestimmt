ALTER TABLE `bb1_votes` ADD `voteid` VARCHAR(255) NOT NULL;

ALTER TABLE `bb1_boards` ADD `allowwhovote` int(11) unsigned NOT NULL default '0';