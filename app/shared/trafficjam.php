<?php

    /**
     * This is the TrafficJam object. It can be used to store or fetch data to and from the database.
     */
    class TrafficJam {
 
        // Database connection;
        private $conn;

        // Properties.
        private $id;
        private $from;
        private $fromLocLat;
        private $fromLocLon;
        private $to;
        private $toLocLat;
        private $toLocLon;
        private $start;
        private $delay;
        private $distance;
        private $timestamp;
        private $roadName;
        private $segments;
 
        // Constructor, pass the database.
        public function __construct(PDO $db) {

            $this->conn = $db;
        }

        // Insert TrafficJam.
        function insert() {
    
            $query = 'INSERT INTO trafficjam SET '
                    .'`from`=:from, '
                    .'from_loc_lat=:fromLocLat, '
                    .'from_loc_lon=:fromLocLon, '
                    .'`to`=:to, '
                    .'to_loc_lat=:toLocLat, '
                    .'to_loc_lon=:toLocLon, '
                    .'start=:start, '
                    .'delay=:delay, '
                    .'distance=:distance, '
                    .'timestamp=:timestamp,'
                    .'road_name=:roadName';
    
            // Prepare query.
            $stmt = $this->conn->prepare($query);
    
            // Sanitize.
            $this->from = htmlspecialchars(strip_tags($this->from));
            $this->fromLocLat = htmlspecialchars(strip_tags($this->fromLocLat));
            $this->fromLocLon = htmlspecialchars(strip_tags($this->fromLocLon));
            $this->to = htmlspecialchars(strip_tags($this->to));
            $this->toLocLat = htmlspecialchars(strip_tags($this->toLocLat));
            $this->toLocLon = htmlspecialchars(strip_tags($this->toLocLon));
            $this->start = htmlspecialchars(strip_tags($this->start));
            $this->delay = htmlspecialchars(strip_tags($this->delay));
            $this->distance = htmlspecialchars(strip_tags($this->distance));
            $this->timestamp = htmlspecialchars(strip_tags($this->timestamp));
            $this->roadName = htmlspecialchars(strip_tags($this->roadName));
    
            // Bind values.
            $stmt->bindParam(':from', $this->from);
            $stmt->bindParam(':fromLocLat', $this->fromLocLat);
            $stmt->bindParam(':fromLocLon', $this->fromLocLon);
            $stmt->bindParam(':to', $this->to);
            $stmt->bindParam(':toLocLat', $this->toLocLat);
            $stmt->bindParam(':toLocLon', $this->toLocLon);
            $stmt->bindParam(':start', $this->start);
            $stmt->bindParam(':delay', $this->delay);
            $stmt->bindParam(':distance', $this->distance);
            $stmt->bindParam(':timestamp', $this->timestamp);
            $stmt->bindParam(':roadName', $this->roadName);

            // Execute query.
            if ($stmt->execute()) {

                return true;
            }

            return false;
        }

        // Select 1 TrafficJam, including historical data.
        function select() {

            $query = 'SELECT '
                    .'trafficjam.id, '
                    .'trafficjam.from, '
                    .'trafficjam.from_loc_lat, '
                    .'trafficjam.from_loc_lon, '
                    .'trafficjam.to, '
                    .'trafficjam.to_loc_lat, '
                    .'trafficjam.to_loc_lon, '
                    .'trafficjam.start, '
                    .'trafficjam.delay, '
                    .'trafficjam.distance, '
                    .'trafficjam.timestamp, '
                    .'trafficjam.road_name '
                    .'FROM trafficjam '
                    .'JOIN trafficjam tj ON tj.from = trafficjam.from AND tj.to = trafficjam.to AND tj.start = trafficjam.start AND tj.id = ? ';
    
            // Prepare statement.
            $stmt = $this->conn->prepare($query);
    
            // Bind id parameter.
            $stmt->bindParam(1, $this->id);
    
            // Execute query.
            $stmt->execute();
    
            return $stmt;
        }
        
        // Try to find the id of 1 historical data record. Only if at least one record is found, historical data of current trafficjams can be shown.
        function findId() {

            $query = 'SELECT '
                    .'MAX(trafficjam.id) AS id, '
                    .'COUNT(trafficjam.id) AS segments '
                    .'FROM trafficjam '
                    .'WHERE trafficjam.from = ? AND trafficjam.to = ? AND trafficjam.start = ?';
    
            // Prepare statement.
            $stmt = $this->conn->prepare($query);
    
            // Bind search parameters.
            $stmt->bindParam(1, $this->from);
            $stmt->bindParam(2, $this->to);
            $stmt->bindParam(3, $this->start);
    
            // Execute query.
            $stmt->execute();
    
            return $stmt;
        }
        
        // Select multiple TrafficJams on a given date and time.
        function selectMultiple(string $datetime, int $limit, int $offset) {

            $query = 'SELECT DISTINCT '
                    .'(SELECT COUNT(total.id) FROM trafficjam total WHERE total.from = trafficjam.from AND total.to = trafficjam.to AND total.start = trafficjam.start) AS segments, '
                    .'trafficjam.from, '
                    .'trafficjam.from_loc_lat, '
                    .'trafficjam.from_loc_lon, '
                    .'trafficjam.to, '
                    .'trafficjam.to_loc_lat, '
                    .'trafficjam.to_loc_lon, '
                    .'trafficjam.start, '
                    .'trafficjam.road_name, '
                    .'(SELECT MAX(id) FROM trafficjam tj WHERE tj.from = trafficjam.from AND tj.to = trafficjam.to AND tj.start = trafficjam.start) AS id, '
                    .'(SELECT delay FROM trafficjam tj2 WHERE tj2.from = trafficjam.from AND tj2.to = trafficjam.to AND tj2.start = trafficjam.start AND tj2.timestamp >= ? ORDER BY tj2.timestamp ASC LIMIT 0, 1) AS delay, '
                    .'(SELECT distance FROM trafficjam tj3 WHERE tj3.from = trafficjam.from AND tj3.to = trafficjam.to AND tj3.start = trafficjam.start AND tj3.timestamp >= ? ORDER BY tj3.timestamp ASC LIMIT 0, 1) AS distance '
                    .'FROM trafficjam '
                    .'WHERE trafficjam.start <= ? AND trafficjam.timestamp >= ? '
                    .'LIMIT ?, ?';

            // Prepare statement.
            $stmt = $this->conn->prepare($query);

            // Bind from and to parameters.
            $stmt->bindParam(1, $datetime);
            $stmt->bindParam(2, $datetime);
            $stmt->bindParam(3, $datetime);
            $stmt->bindParam(4, $datetime);
            $stmt->bindParam(5, $offset, PDO::PARAM_INT);
            $stmt->bindParam(6, $limit, PDO::PARAM_INT);

            // Execute query.
            $stmt->execute();
    
            return $stmt;
        }

        // Setters.
        public function setId(int $id) {

            $this->id = $id;
        }

        public function setFrom(string $from) {

            $this->from = $from;
        }

        public function setFromLocLat(string $fromLocLat) {

            $this->fromLocLat = $fromLocLat;
        }

        public function setFromLocLon(string $fromLocLon) {

            $this->fromLocLon = $fromLocLon;
        }

        public function setTo(string $to) {

            $this->to = $to;
        }

        public function setToLocLat(string $toLocLat) {

            $this->toLocLat = $toLocLat;
        }

        public function setToLocLon(string $toLocLon) {

            $this->toLocLon = $toLocLon;
        }

        public function setStart(string $start) {

            $this->start = $start;
        }

        public function setDelay(int $delay) {

            $this->delay = $delay;
        }

        public function setDistance(int $distance) {

            $this->distance = $distance;
        }

        public function setTimestamp(string $timestamp) {

            $this->timestamp = $timestamp;
        }

        public function setRoadName(string $roadName) {

            $this->roadName = $roadName;
        }

        public function setSegments(int $segments) {

            $this->segments = $segments;
        }
    }

?>