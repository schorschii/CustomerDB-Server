# Agent-Server Communication Method
Architecture Decision Record  
Lang: en  
Encoding: utf-8  
Date: 2022-11-01  
Author: Georg Sieber

## Decision
All timestamps are stored in UTC time in the database on the server.

## Status
Accepted

## Context
Since the Customer Database app targets users all around the world, timestamps must be in UTC time to avoid time zone conflicts.

## Consequences
All communication over the API must also use UTC timestamps.
