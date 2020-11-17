# View-Statistics Change-Log

## 2020-11-17  Release of version 2.0.1

Security fix - please update ASAP

*   [BUGFIX] Remove request params logging



## 2020-10-08  Release of version 2.0.0

*   [TASK] Move default object type to settings
*   [TASK] Migrate the extension for TYPO3 9.5
*   [!!!][TASK] Increase length of IP field in the database to accept IPv6 addresses. This changes the database structure.
*   [FEATURE] Make tracking user agents and login duration configurable in the extension settings
*   [BUGFIX] Fix "class 'CodingMs\ViewStatistics\ViewHelpers\Format\LoginDurationViewHelper' does not have a method 'render'" for TYPO3 8
*   [BUGFIX] Fix path to JavaScript file
*   [BUGFIX] Add missing label for user agent
*   [TASK] Add translation files for documentation
*   [TASK] Set default values if no extension settings exist
*   [TASK] Database and ORM migration
*   [TASK] ViewHelper migration
*   [TASK] Source code clean up
*   [TASK] Remove inject annotations
*   [TASK] Add configuration to track immobilien/properties from openimmo extension (realty, estate)
*   [TASK] Replace $_EXTKEY variable by static extension key string
*   [TASK] Add documentation files
*   [TASK] Clean up ChangeLog file



## 2020-05-27 Release of Version 1.0.4

*   [TASK] Cleanup Change-Log



## 2019-10-13  Release of version 1.0.3

*	[TASK] Add Gitlab-CI configuration.
*	[TASK] Providing a documentation about configuring own tracking objects.
*	[FEATURE] Track user agent of requests.



## 2017-11-23  Release of version 1.0.2

*	[BUGFIX] Fixing of tracking IP addresses
*	[BUGFIX] Adding group by in object SQL statement



## 2017-11-20  Release of version 1.0.1

*	[BUGFIX] Fixing sort ViewHelper



## 2017-11-19  Release of version 1.0.0

*	[FEATURE] Tracking IP-Address optionally
*	[FEATURE] Tracking for Referrer, Request-URI and language
*	[FEATURE] Restrictions for non admins
*	[TASK] Translations
*	[TASK] CSV-Export for Tracks and Frontend-User
*	[TASK] Backend-List for custom objects
