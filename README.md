BARTER
======

The BARTER project suit consists of 2 mobile user interfaces (android_terminal and customer_app), 1 centralised mysql database and a responsive web dashboard. These platforms make up the BARTER system; the mobile and web dash have been designed to operate independently from one another, this means that if the web dash is not required users do not have to set it up on your server.

Although some libraries were used within the project (this is noted within the code), the majority of the code, design and development for this project was developed by the following people 

<a href="http://www.marklochrie.com" target="_blank">Mark Lochrie</a><br />
<a href="http://www.research.lancs.ac.uk/portal/en/people/adrian-gradinar(f0dd140d-16ae-4ec5-8250-c4341893c5c2).html" target="_blank">Adrian Gradinar</a><br />
<a href="http://www.research.lancs.ac.uk/portal/en/people/jonny-huck(14f8d28d-33f1-403c-a5a7-086c2de609c0).html" target="_blank">Jonny Huck </a>

<h2>Android Terminal:</h2>
This is the application which the traders use on their NFC enabled Android device. The application has been designed and tested to work on KitKat, but there is no reason why it should not work on Lollipop (maybe just a recompile). The application was developed under Android Studio, so this IDE is advised for simplicity.

<h2>Config:</h2>
These are the configurations files needed for the project, it consists of the database connection handler, js and css libraries, session handling and also any additional assets such as fonts. 

<h2>Database:</h2>
Written for MySQL, there is a dump of all the tables found within this sql file. The main tables to concentrate on for this project are the tables written with the tbl prefix. 

tbl_transactions - a table containing all the transactions in the system
tbl_users - a table containing all the users in the system
tbl_redeems - a table containing all the redeem transactions in the system.
tbl_associations - a table associating two users, similar to a supplementary credit card (when you have two cards linked to the same account). This can be used when a trader wants his/her employees to count towards their trading data.
tbl_snapshot - this is now a legacy table, and was removed from the system, this table contained data on a snapshot of what the traders thought they were trading at, for example what percent of their trades were local versus non local. 
tbl_customer_totals - as it suggests, this table stores all the data for the trader to know exactly how much a specific customer has spent with them.

<h2>Dashboard:</h2>
This directory contains all the scripts, html and js that is used specifically with the dashboard platform. 

<h2>Dev:</h2>
As this research project was an in the wild experiment, some development work never made it for public consumption. In particular the loop and flower work. Although the work was tested with perspective users within BARTER it never featured within the live version, but we thought that it would be nice to include it in this repo. Again this is dev work so it should be treated like such. The more finished work within this directory can be found at loop > loop_gen.html (this is a dynamic loop generator tool to educate users of the potential impact of inter-trading). 

<h2>Mobile Scripts:</h2>
Mobile Terminal > this directory contains the scripts required in order to communicate with the Android Terminal. The naming conventions for each file are pretty straightforward. Login, Request Sync, Upload Redeems, Upload Transactions and Force Sync.

Mobile > These scripts are used within the Customer App mainly for demo purposes (as this was the last part of the project some areas of the development weren’t hooked up to the live database). However some scripts do have the ability to connect with the live database such as, login and get_stats.

<h2>Customer App:</h2>
Written using Adobe AIR (15) cross platform tools, with the latest Apache Flex SDK (4.13.0) and Flash Builder for the development of choice for this part of the project. This application was written in AIR to permit for cross platform usage on Android and iOS. The package uses some native features such as Dialogs (these have been compiled under the ANE extension for AIR) but the rest of the code/assets was developed in house.  
	 
***

<h1>The MIT License (MIT)</h1>

Copyright (c) 2014 Mobile Radicals

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.