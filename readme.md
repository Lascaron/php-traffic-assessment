Setting up test/development environment in Docker:
- run docker-compose build
- run docker-compose up -d

    Automatic process of fetching new data:
    - open ./cronjob/traffic.php
    - uncomment line 71 and save
    - go to http://localhost/cronjob/traffic.php (this start the proces that fetches new data every 5 minutes)

    API:
    - http://localhost/v1/traffic_jams.php // Fetch all current trafficjams
    - http://localhost/v1/traffic_jams.php?id=q // Fetch details/historical data for trafficjam with id = q
    - http://localhost/v1/traffic_jams.php?date=yyyymmdd&time=hhmmss&limit=q&offset=x // Show historical data on date and time, with limit of q and offset of x (used for pagination)

    Webapplication:
    - set an active Google Maps Javascript Api key in config.php
    - go to http://localhost for the webapp

    Setting up production environment:

    Note:
    - Some files are shared by the crobjob, webapp and api.
      You can place all files in the shared directory in the same directory as the crobjob, webapp and api.
      You can also make a shared directory and set this directory in the config.php files of the cronjob, webapp and api.

    Database:
    - create a MYSQL database
    - create table `trafficjam` in the created database (use ./test-sql/script.sql)
    - set the correct credentials in ./shared/connection.php

    Automatic process of fetching new data:
    - create a directory on the server and place ./crobjob/traffic.php and ./cronjob/config.php in there.
    - place the files in ./shared in the same directory, or copy directory ./shared to the server.
    - make sure $_SHARED_DIR in config.php is set right ('.' for same directory or other value for different directory)
    - create a cronjob that runs every 5 minutes: */5 * * * * php {path to traffic.php}

    API:
    - create a directory on the server and place ./v1/traffic_jams.php and ./v1/config.php in there.
    - place the files in ./shared in the same directory, or copy directory ./shared to the server.
    - make sure $_SHARED_DIR in config.php is set right ('.' for same directory or other value for different directory)

    Example: when $_SERVER_API and $_API_DIR are respectively set to http://trafficjams and /v1 the api can be used as follows.
    - http://trafficjams/v1/traffic_jams.php // Fetch all current trafficjams
    - http://trafficjams/v1/traffic_jams.php?id=q // Fetch details/historical data for trafficjam with id = q
    - http://trafficjams/v1/traffic_jams.php?date=yyyymmdd&time=hhmmss&limit=q&offset=x // Show historical data on date and time, with limit of q and offset of x (used for pagination)

    Or, when using url rewriting (see .htaccess):
    - http://trafficjams/v1/trafficjams // Fetch all current trafficjams (add ?max=q for a maximum of q results)
    - http://trafficjams/v1/trafficjams/q // Fetch details/historical data for trafficjam with id = q
    - http://trafficjams/v1/trafficjams?date=yyyymmdd&time=hhmmss&limit=q&offset=x // Show historical data on date and time, with limit of q and offset of x (used for pagination)

    Webapplication:
    - create a directory on the server and place the following files and directories in there:
        index.php
        details.php
        config.php
        ./css
        ./js
        ./img
    - place the files in ./shared in the same directory, or copy directory ./shared to the server.
    - make sure $_SHARED_DIR in config.php is set right ('.' for same directory or other value for different directory)
    - set an active Google Maps Javascript Api key in config.php
    - set the correct values for $_SERVER_APP, $_SERVER_API and $_API_DIR in config.php
    Note: you can use the $_API_DIR to point to a certrain version of the api.