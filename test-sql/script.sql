USE assessment;
CREATE TABLE IF NOT EXISTS `trafficjam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(64) NOT NULL,
  `from_loc_lat` varchar(16) NOT NULL,
  `from_loc_lon` varchar(16) NOT NULL,
  `to` varchar(64) NOT NULL,
  `to_loc_lat` varchar(16) NOT NULL,
  `to_loc_lon` varchar(16) NOT NULL,
  `start` datetime NOT NULL,
  `delay` int(10) NOT NULL,
  `distance` int(10) NOT NULL,
  `timestamp` datetime NOT NULL,
  `road_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

