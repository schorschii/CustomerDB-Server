# Upgrade Instructions

## v1.1
- upgrade database schema as defined in lib/customerdb.sql
  - add table Appointment
  - add table Calendar
  - add new table's indices and constraints
  - add column 'files' to Customer table
- replace all old files except conf.php
