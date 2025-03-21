# 世界一役に立たないデーモン
私はPHPerKaigi2025に光を見ました。  
systemdへの直接的な依存は許さない伝統派のために、起動スクリプトを`/usr/local/etc/rc.d`に配置し、サービスの有効・無効は`/etc/rc.conf`で管理する流儀を示します。

## こう
```sh
sudo chmod +x daemon.php | mv daemon.php /usr/local/sbin/myphpdaemon.php
sudo chmod +x daemon-rc | mv daemon-rc /usr/local/etc/rc.d/myphpdaemon
```

## こう
```sh
vi /etc/rc.conf

myphpdaemon_enable="YES"
```

## こう
```sh
myphpdaemon start
Starting daemon...
Daemon started with PID 12345

myphpdaemon status
Daemon is running.
```

## ログ
```sh
tail -f /var/log/myphpdaemon.log
```

FreeBSDしか勝たん。
