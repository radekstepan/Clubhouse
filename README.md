# Clubhouse (April 2010 - May 2010)

A 37Signals' Campfire port into PHP

Works like a chatroom allowing users to share files, images, YouTube videos, code and chat in real time. Conversation transcripts, fine-grained user access, guest rooms and a snappy AJAX interface is a cherry on top.

## Getting started

Make sure `/log` is writable and that `/db/database.db` exists and is writable too. Database access is configured to use `pdo_sqlite` by default, you can check its existence like so:

```php
<?php
phpinfo();
?>
```

Visit the (http://127.0.0.1/clubhouse/installation)[http://127.0.0.1/clubhouse/installation] to initially populate the database, alternatively use the database packaged in this repo.

## Troubleshooting

On PHP 5.4+ you will get "call-time pass-by-reference" error.

Fari Framework automatically understands that you are in development mode, if you call the app from `127.0.0.1`. Do so to see a stacktrace of where an error has happened instead of seeing a placeholder error message.