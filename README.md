# UNCC MediaWiki Utils
- Tags: mediawiki
- Version: 1.0.0
- Author: Crystal Barton
- Description: Contains the scripts and source code needed to maintain the UNC Charlotte's
  implementation of MediaWiki.

## Installation

1.  Clone into a folder on the web server.
1.  Create copy of _config/admin.default.php_ and rename _config/admin.php_.
    Go through _admin.php_ and configure settings.
1.  Create copy of _config/mediawiki.default.php_ and rename _config/mediawiki.php_.
    Got through _mediawiki.php_ and configure settings.
1.  Run _clone-repo.php_.  `php clone-repo.php --all`
1.  Create wikis (see "Create Wiki" section below).



## Usage

TODO


### Clone / Update Repositories

__Clone all repositories__

`php clone-repo.php --all`

__Clone an individual repository__

`php clone-repo.php --repo:[name]`
`php clone-repo.php --repo:mediawiki`


### Create Wiki

_Ensure that the repositories are up-to-date._

`php create-wiki.php --name:[name] --title:'[title]'`
`php create-wiki.php --name:clas --title:'Clas Wiki'` 

### Update Wiki(s)

_Ensure that the repositories are up-to-date._

__Update all wikis__

`php update-wiki.php --all`

__Update an individual wiki__

`php update-wiki.php --wiki:[name]`
`php update-wiki.php --wiki:clas`

### Delete Wiki

_Ensure that the repositories are up-to-date._

`php delete-wiki.php --wiki:[name]`
`php delete-wiki.php --wiki:clas`





