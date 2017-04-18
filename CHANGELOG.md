# Changelog for v4.1

4.6.3 - 16 October 2016

- Added a convenient method for having a default return when there is no record and firstRecord is called

4.6.2 - 10 October 2016

- Fixes Issue 54 (inconsistent behavior of getRecord on empty cursor between http and bolt

4.6.0 - 01 October 2016

- Client class parameterizable

4.4.5 - 05 July 2016

- Fixed an issue with `relationshipValue()`

4.4.4

- Added preflight to stack

4.4.3 - 09 June 2016

- Fixed same issue as 4.4.2 in a transaction

4.4.2 - 09 June 2016

- Fixed an issue where empty nested arrays were not converted to json objects

4.4.1 - 06 June 2016

- Upgraded to latest commons

4.4.0 - 28 May 2016

- Added getLabels method to the client

4.3.1 - 13 May 2016

- Added the possibility to pass a default value to `Record::get()` to be returned if the record doesn't contains the given key

4.2.0 - 06 May 2016

- Added events dispatching before and after running statements and stacks

4.1.1 - 06 May 2016

- Added `registerExistingConnection` in ConnectionManager

4.1.0 - 02 May 2016

- Added `updateStatistics()` method on the ResultCollection for combined statistics of stacks, transactions, etc..

4.0.2 - 28 Apr 2016

- Fixed a bug where relationships deleted count was not hydrated in the http result update statistics

4.0.1 - 27 Apr 2016

- Fixed a bug where `nodeValue` was using a hardcoded identifier [8bf11473c9870c2423de2763622d2674b97216db](8bf11473c9870c2423de2763622d2674b97216db)

4.0.0 - 25 Apr 2016

Initial 4.0 release for support with Neo4j 3.0
