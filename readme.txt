=== Covid-19 Statistics Displayer ===
Requires at least: 2.5
Requires PHP: 5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Contributors: carlsansfa
Tags: covid, covid-19, statistic
Tested up to: 5.4.1
Stable tag: 1.2

== Description ==
This plugin allow you to display the latest covid statistics and previsions for the entire world using [covidstats] shortcode on the page where you want the stats to display.
Note that this plugin use third party data from my website.
This data include the graphics that are generated each day by a R script and an up to date list of all the country, states/provinces and city.
The data server keep an ip address list for statistic and security purpose only.
This plugin doesn't send any of your website data or any personal information to me or to anyone.
It just display daily statistics and use a simple prevision tool.
All the data used to create graphics come from the COVID-19 Data Repository by the Center for Systems Science and Engineering (CSSE) at Johns Hopkins University :
https://github.com/CSSEGISandData/COVID-19
The previsions and data are only informational and may be wrong.
All the php and R code will soon be available so you can modify and do whatever you want with it.
Thanks for the interest that you put in my project.

The png graphic pages and json file can be accessed in this folder : http://moduloinfo.ca/covid/graphs/
Link to my privacy policy : http://moduloinfo.ca/wordpress/privacy-policy/
== Installation ==
Add the [covidstats] shortcode to your page.

== Screenshots ==
1. What it look like
2. How to install


== Changelog ==
1.2 Change log
- Better shortcode integration

1.1 Change log
- Recovered graphics don\'t show up anymore if there is no data
- More efficient data integration to reduce server load
