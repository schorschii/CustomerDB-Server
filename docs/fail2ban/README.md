# fail2ban Filter for Customer Database Server

## Installation
1. Install `fail2ban`
2. Copy `filter/customerdb.conf` to `/etc/fail2ban/filter.d/customerdb.conf`
3. Copy `jails/customerdb.conf` to `/etc/fail2ban/jail.d/customerdb.conf`
4. Apply new config: `service fail2ban restart`
5. Check `fail2ban-client status` - it should show the customerdb jail active

## Useful Commands
- `fail2ban-regex /var/log/apache2/error.log /etc/fail2ban/filter.d/customerdb.conf`  
  Check if log lines that matches the filter

- `fail2ban-client status customerdb`  
  Show currently banned IPs

- `fail2ban-client set customerdb unbanip x.x.x.x`  
  Unban IP
