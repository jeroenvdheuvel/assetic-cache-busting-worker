README
======

Master: [![Build Status](https://travis-ci.org/jeroenvdheuvel/assetic-cache-busting-worker.svg?branch=master)](https://travis-ci.org/jeroenvdheuvel/assetic-cache-busting-worker)

Description
-----------
This library provides an easy way to bust file cache for assetic. When this worker is hooked-up this worker will add a
hash to the file name based on the file content. When the file content changes the hash changes as well.

This can be setup in Symfony by adding the worker like this:
```yml
services:
     cache_busting:
         class: jvdh\AsseticWorker\CacheBustingWorker
         tags:
             - { name: assetic.factory_worker }
```
