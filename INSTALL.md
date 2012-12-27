Installing phpqbt
=============

- Unzip (or if you're feeling brave, `git clone`) to a location of your choice.
- Insert `misc/db_structure.sql` into a MySQL server. Run it as root; it creates the phpqbt database.
- **It is recommended you create a normal user with full access to just the phpqbt database.**
- Edit `EMWIN/config.php` to reflect your own database settings and other information

**Make changes in the following places to account for your own database settings:**

- `emwin.sh` line 9

Running phpqbt
============

From the directory where you unzipped the package, simply type the following command:

`nohup ./emwin.sh &`

Now what?
========

A vanilla installation of phpqbt will just connect to an EMWIN server, ingest data, and do nothing. You can very easily extend the software to process various products. See the [Extending phpqbt](https://github.com/kirkmawa/phpqbt/wiki/Extending-phpqbt) wiki page on GitHub for more details.

