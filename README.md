# PowerADM

## About
PowerADM is a Symfony-based PowerDNS administration tool. It is designed to be a simple, easy-to-use, and lightweight tool for managing PowerDNS servers.

This project is still in the early stages of development and is not yet ready for production use.

## Features
- Forward and reverse DNS zone management
- Zone templates
- User management with per zone permissions and roles
  - Authentication:
	- Local
	- OIDC
- Audit Log
- View PowerDNS statistics and server configuration

## Installation

### Docker
TBD

### Manual Installation

#### Requirements
 - PHP 8.2 or later
 - Composer
 - MariaDB (other DBMS may work but are untested)

#### Installation
```bash
composer create-project poweradm/poweradm path/to/install
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.
