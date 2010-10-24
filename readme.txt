=== Sell from Blog ===
Contributors: paulpela
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=XC8GDD4EMJK98
Tags: sell, ebook, premium, sms, paid content, payments
Requires at least: 3.0
Tested up to: 3.0.1
Stable tag: 0.87

Sell from Blog lets you sell your ebook or software package via premium SMS payments.

== Description ==

Opis w języku polskim: http://www.blogworkorange.net/sell-from-blog/

This plugin lets you sell your ebook, software package or anything else small enough to fit into a standard email message (less than 10MiB is safe).

The buyer obtains a code through premium sms service and enters it along with his or her email addres in the form. If the code is correct, Sell from Blog creates a message, attaches your package to it and sends it to the buyer's email address.

The package you want to sell is placed on your own server, in a secured directory which is not accessible from outside.

There is also a dashboard widget which shows you how many codes you have left and gives detailed info on last 25 transactions.


== Installation ==

<ol>
<li>Download the package with the newest version of Sell from Blog from the official WordPress.org repository</li>
<li>Go to Plugins->Add new menu</li>
<li>Upload tha plugin to your server and activate it</li>
</ol>

= Configuration =

Configuration menu is at `Settings->Sell from Blog`

<ol>
<li>Sign up with a premium SMS service provider which will give you a package of codes for you to validate against.</li>
<li>Enter the codes in the configuration panel, comma separated.</li>
<li>Create a secret directory inside your WordPress installation, for exmaple `/secret32Dfk8lcDR/` and set it to be readable only by you and the web server (750, rwxr-x---)</li>
<li>Put an empty index.html file inside the folder.</li>
<li>Put your file inside the directory and make it readable only by you and the web server (640, rw-r-----)</li>
<li>Put a `.htaccess` file into this directory, containing only this line: `deny from all` (if you are on a web server which does not support .htaccess, find othe way to block access to this directory from outside)</li>
<li>Put the path to the file in the configuration panel, for example: `/secret32Dfk8lcDR/my-ebook.zip`</li>
<li>It's a good idea to compress your file beforehand</li>
<li>Configure the confirmation message and the email message</li>
<li>Write a sales page and put `[sell-from-blog]` shortcode wherever you want the form to appear.</li>
<li>Remember to inform your buyers how to obtain the premium code, and to put a TOC of the premium sms service</li>
</ol>

== Frequently Asked Questions ==

= With which premium sms services does the plugin currently work with? =

It works with every service which provides you with a set of codes and lets you check user input against them by yourself.

I tested it with:

* https://www.mobilepay.pl/ (pack)

= How many files can I sell at the same time through this plugin? =

Currently, the plugin supports selling only one file at any given time. Therefore, it's only good for people who have one ebook or program to sell on one blog.

= Does my package file have to be of a certain type? =

No, it can be of any type (pdf, exe, zip, doc, etc.). But it is a good idea to compress it, because there are limits to the size of email messages (often a message must be less than 10MiB).

= Can I add my own codes to give away to my readers? =

Yes, you can add your own custom (promotional) codes.

= Can you add support for remote validation of codes? =

I will add support for remote validation in future versions. You can help by sending me links to technical data of your service provider.

= Which languages are supported? =

Currently:

* English
* Polish (pl_PL)

This plugin is gettext-ready. You can easily translate it into your language.

== Screenshots ==

1. The form
2. Configuration menu

== Changelog ==

= 0.87 =
* Sends notification to admin after each transaction (can be turned off)
* Additional data is saved to the database after each transaction (code, email, date, IP)
* A Dashboard widget shows you how many codes you have left and info on last 25 transactions

= 0.86 =
* Polish (pl_PL) translation added.

= 0.85 =
* Polish (pl_PL) translation added (error again).

= 0.84 =
* Polish (pl_PL) translation added (error).

= 0.83 =
* Localization ready.

= 0.82 =
* Localization tweaks.

= 0.81 =
* Something went wrong with 0.80 ;)

= 0.80 =
* First public release.

== Upgrade Notice ==

= 0.87 =
* New features: admin notification, dashboard widget with statistics

= 0.86 =
* Polish (pl_PL) translation added.

= 0.81 =
* Something went wrong with 0.80 ;)
