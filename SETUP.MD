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

Enable Service
```bash
sudo systemctl enable apache2
sudo systemctl enable cups
```



===============================
---

# 3. Install CUPS Print Server

```bash
sudo apt install cups -y
```

Enable service:

```bash
sudo systemctl enable cups
sudo systemctl start cups
```

Check service status:

```bash
sudo systemctl status cups
```

---

# 4. Configure CUPS

Edit configuration file:

```bash
sudo nano /etc/cups/cupsd.conf
```

Allow network access:

```
Listen 0.0.0.0:631
Browsing On
```

Restart CUPS:

```bash
sudo systemctl restart cups
```

---

# 5. Access Web Interface

Open browser:

```
http://SERVER_IP:631
```

Login using system administrator credentials.

---

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
