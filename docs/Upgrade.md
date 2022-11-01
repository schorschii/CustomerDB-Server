# Upgrade Instructions

## v1.3
- upgrade database schema as defined in `lib/customerdb.sql`
  - add column 'last_modified_on_server' to Customer, Voucher, Calendar, Appointment and Setting table

## v1.2
- upgrade database schema as defined in `lib/customerdb.sql`
  - add column 'customer_id' to Appointment table
  - add column 'from_customer_id' and 'for_customer_id' to Voucher table
- replace all old files except conf.php

## v1.1
- upgrade database schema as defined in `lib/customerdb.sql`
  - add table Appointment
  - add table Calendar
  - add new table's indices and constraints
  - add column 'files' to Customer table
- replace all old files except conf.php
