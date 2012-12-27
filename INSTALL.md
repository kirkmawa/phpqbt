Installing phpqbt
=============

- Unzip (or if you're feeling brave, `git clone`) to a location of your choice.
- Insert `misc/db_structure.sql` into a MySQL server. Run it as root; it creates the phpqbt database.
- **It is recommended you create a normal user with full access to just the phpqbt database.**

**Change the code in the following places to account for your own database settings:**

- `emwin.sh` line 9
- `restartemwin.sh` line 5
- `EMWIN/speedcheck.php` lines 5 and 48
- `EMWIN/lib/db.php` line 6

**Also consider changing the information in line 59 of `EMWIN/input/ByteBlaster.php` to reflect your email address. You must keep the NM- in the string for your client to be considered valid.**

Running phpqbt
============

From the directory where you unzipped the package, simply type the following command:

`nohup ./emwin.sh &`

