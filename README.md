tanklab

World of Tanks Statistics site

Originally the tanks.ofscience.net site was hacked together for my own use - it grew way faster than I ever expected
at the end it was tracking over 100,000 tankers across  three of the world of tanks regions. I no longer have the time
or the resources to host this thing & change it up so that it actually handles that many users effectively. 

This code was never intended for public consumption, it was hacked together very quickly for my own use however 
i've received enough requests for it that I'm going to put it out there so you guys can do what you will with it.

Lots of hard-coded paths and other uglyness through out - 

Install - 

1) import the SQL into your Database
2) set your database credentials in the tanks_db.in.php files
3) cruise through the source files and fix up any paths that might need to be changed
  it was originally installed in /home/tanks/tanks.ofscience.net/ (web root)
4) the loadTanksInfo.php should refresh the tank Definitions in the DB (check the code, there are get parameters
  for the differnt servers eu,na, and sea
5) cron up the updateStats.php and it should start scraping data based on the users it finds
6) add your self or your clan to the site and tracking should begin
7) Let me know if you decide to use it (just for my own curiosity)

that should do it

Happy Tankin'
