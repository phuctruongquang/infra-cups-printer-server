# 🖨 Centralized Printing Management System

### CUPS Print Server with Web Reporting

---

# 📌 Project Overview

This project implements a **centralized printing management system** using **CUPS (Common Unix Printing System)** deployed on an **Ubuntu virtual machine** running on **Hyper-V**.

The system allows multiple departments to print through a centralized print server while administrators can monitor printing activity via a **web interface** and receive **automated monthly usage reports via email**.

This architecture improves:

* Print management
* Resource control
* Usage monitoring
* Operational efficiency

---

# 🏗 System Architecture

<img src="docs/images/Diagram_CUPS_printer.png" width="900">

The architecture consists of a centralized print server connected to network printers and client workstations across multiple departments.

---

# 🖥 Infrastructure

## Host Environment

| Component      | Technology     |
| -------------- | -------------- |
| Server         | Windows Server |
| Virtualization | Hyper-V        |

---

## Virtual Machine

### Ubuntu_VM_01

Services deployed:

* CUPS Print Server
* Apache2 Web Server
* Print Log Parser
* Automated Reporting System

---

# 🖨 Printing System

## Print Server

The printing system is managed by **CUPS**, which handles:

* Printer queue management
* Job scheduling
* Print job logging
* Client connections

---

## Network Printers

| Printer    | Protocol      | Port     |
| ---------- | ------------- | -------- |
| Printer-01 | JetDirect RAW | TCP 9100 |
| Printer-02 | JetDirect RAW | TCP 9100 |

---

# 👥 Client Access

Multiple departments access the centralized print server.

Departments:

* IT
* Marketing
* HR

Supported printing protocols:

| Protocol                         | Port |
| -------------------------------- | ---- |
| IPP (Internet Printing Protocol) | 631  |
| SMB Printing                     | 445  |

Users send print jobs from their workstations to the CUPS server.

---

# 🔄 Printing Workflow

1️⃣ User sends a print job from workstation

2️⃣ Job is transmitted to the **CUPS Print Server**

Protocols used:

* IPP
* SMB

3️⃣ CUPS processes the job and places it in the print queue

4️⃣ The server forwards the job to the printer via:

```
JetDirect RAW (TCP 9100)
```

5️⃣ Printer executes the job

6️⃣ Print logs are recorded for reporting and monitoring

---

# 📊 Print Monitoring & Reporting

The system collects printing logs and generates **monthly usage reports**.

Features include:

* Print job tracking
* User activity logging
* Monthly usage reports
* Automated email reporting
* Web dashboard for administrators

Reports allow administrators to:

* Monitor printer usage
* Track department printing activity
* Detect excessive printing

---

# 🌐 Web Management Interface

The web interface is hosted using **Apache2**.

Administrator capabilities:

* View printing logs
* Access usage reports
* Monitor printer activity
* Download monthly reports

Access method:

```
HTTP Web Interface
```

---

# 📧 Automated Email Reporting

The system automatically generates **monthly printing reports** and sends them to the administrator via email.

Report includes:

* Total print jobs
* Printer usage
* Department usage
* Activity statistics

---

# 🔐 Security Considerations

Security practices implemented:

* Controlled access to print server
* Printer access limited to internal network
* Role-based administrator monitoring
* Log-based activity tracking

---

# 🛠 Technology Stack

| Layer              | Technology          |
| ------------------ | ------------------- |
| Virtualization     | Hyper-V             |
| Operating System   | Ubuntu Server       |
| Print Server       | CUPS                |
| Web Server         | Apache2             |
| Protocols          | IPP, SMB, JetDirect |
| Reporting          | Custom Log Parser   |
| Email Notification | Mail System         |

---

# 📂 Project Structure

```
cups-print-server/
│
├── README.md
│
├── docs
│   └── images
│       └── print-system-architecture.png
│
├── scripts
│   └── log-parser
│
├── web
│   └── dashboard
│
└── reports
    └── monthly-reports
```

---

# 🚀 Future Improvements

Possible improvements for the system:

* Printer quota management
* Department usage limits
* Grafana print monitoring dashboard
* Integration with LDAP / Active Directory
* High availability print servers
* Real-time monitoring

---

# 👤 Author

**Truong Quang Phuc**
System Engineer | DevOps

---
