GIMS
=======================
Master: [![Build Status](https://api.travis-ci.org/Ecodev/gims.svg?branch=master)](http://travis-ci.org/Ecodev/gims) Develop: [![Build Status](https://api.travis-ci.org/Ecodev/gims.svg?branch=develop)](http://travis-ci.org/Ecodev/gims)

Introduction
------------
GIMS application using the ZF2.


Installation
------------

1. The recommended way to get a working copy is the following:

```
./bin/install_dependencies.sh
./vendor/bin/phing build
```

2. Create a database in PostgreSQL named "gims"
3. Configure database in ``config/autoload/local.php``
4. Set up a virtual host to point to ``htdocs/`` directory


Testing
-------

Full testing can be executed via Phing. This WILL DESTROY existing database:

```
./vendor/bin/phing test
```

See [detailed informations](tests/README.md) for advanced usage.
