# Stream M for Zewall

## Compiling
```bash
$ export LC_MESSAGES=en_US
$ ant
```

## Installing
```bash
$ scp dist/stream-m-rXXX-YYYYMMDD.zip user@172.20.180.180:/tmp
$ ssh user@172.20.180.180
user@172.20.180.180 $> cd /tmp/
user@172.20.180.180 $> unzip -d stream-m-rXXX-YYYYMMDD stream-m-rXXX-YYYYMMDD.zip
user@172.20.180.180 $> cd stream-m-rXXX-YYYYMMDD
user@172.20.180.180 $> vim install.sh
user@172.20.180.180 $> sudo bash install.sh
```

## Running
```bash
user@172.20.180.180 $> java -cp /usr/local/stream-m/lib/stream-m.jar StreamingServer /etc/stream-m/server.conf
```

## Original README from Stream-m

See http://code.google.com/p/stream-m/
