# System Setup Guide

This guide describes the steps required to deploy the centralized printing system using CUPS and Apache.

---

# 1. Install Ubuntu Server

Create a virtual machine on Hyper-V.

Recommended configuration:

CPU: 2 cores
RAM: 4 GB
Disk: 40 GB

Install **Ubuntu Server 22.04 LTS**.

---

# 2. Update & Install packet

```bash
sudo apt update

sudo apt install -y \
apache2 \
php \
php-cli \
cups \
cups-client \
logrotate \
mailutils
```
image 1-2

Enable Service
```bash
sudo systemctl enable apache2
sudo systemctl enable cups
```



===============================
---

# 3. Config CUPS

```bash
nano /etc/cups/cupsd.conf
```

Change Listen:
Image 3

Config web admin:

image 4

```bash
LogLevel warn
Port 631
Listen /run/cups/cups.sock
Browsing Off
BrowseLocalProtocols dnssd
DefaultAuthType Basic
DefaultEncryption Never
WebInterface Yes
<Location />
  Allow all
</Location>
<Location /admin>
  AuthType Basic
  Require user @SYSTEM
  Order allow,deny
  Allow all
</Location>
<Location /admin/conf>
  AuthType Basic
  Require user @SYSTEM
  Order allow,deny
  Allow all
</Location>
<Policy default>
  # Job-related operations must be done by the owner or an administrator...
  <Limit Send-Document Send-URI Hold-Job Release-Job Restart-Job Purge-Jobs Set-Job-Attributes Create-Job-Subscription Renew-Subscription Cancel-Subscription Get-Notifications Reprocess-Job Cancel-C>
    Require user @OWNER @SYSTEM
    Order deny,allow
  </Limit>
  # All administration operations require an administrator to authenticate...
  <Limit Pause-Printer Resume-Printer Set-Printer-Attributes Enable-Printer Disable-Printer Pause-Printer-After-Current-Job Hold-New-Jobs Release-Held-New-Jobs Deactivate-Printer Activate-Printer Re>
    AuthType Default
    Require user @SYSTEM
    Order deny,allow
</Limit>
  # Only the owner or an administrator can cancel a job...
  <Limit Cancel-Job>
    Order deny,allow
    Require user @OWNER @SYSTEM
  </Limit>
  <Limit All>
  Order deny,allow
  </Limit>
</Policy>
MaxClients 100
MaxJobs 0
```

Restart service:
```bash
sudo systemctl restart cups
```
Add user to Lpadmin
```bash
sudo usermod -aG lpadmin your_user
```

Access web page:
```bash
http://IP:631
```
imgae 5
=================================================


# 4. Log Rotation Configuration

Edit time save log rotate 7 change to 60
```bash
sudo nano /etc/logrotate.d/cups-daemon
```
Image 6


======================

# 5. Create Dashboard Directory
Create Dashboard managemnet
```bash
mkdir -p /var/www/html/cups-report
cd /var/www/html/cups-report
```

Create file
```bash
touch parser.php index.php cache.json monthly_report.php
```

```bash
```

```bash
```

```bash
```

```bash
```

```bash
```

```bash
```



# 6. Add Network Printers

Add printers using **JetDirect (RAW)** connection.

Example:

Printer-01

```
socket://PRINTER_IP:9100
```

Printer-02

```
socket://PRINTER_IP:9100
```

---

# 7. Install Apache Web Server

```bash
sudo apt install apache2 -y
```

Start service:

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Test access:

```
http://SERVER_IP
```

---

# 8. Deploy Print Report Web Interface

Copy web files to:

```
/var/www/html/
```

Restart Apache:

```bash
sudo systemctl restart apache2
```

---

# 9. Configure Monthly Report Script

Create cron job:

```bash
crontab -e
```

Add task:

```
0 1 1 * * /scripts/send_report.sh
```

This task will generate and send the monthly report automatically.

---

# 10. Verify System

Check the following:

* CUPS service running
* Printers accessible
* Web dashboard accessible
* Reports generated successfully
* Email notification working

---

# Deployment Completed

The centralized printing system is now ready for use.
