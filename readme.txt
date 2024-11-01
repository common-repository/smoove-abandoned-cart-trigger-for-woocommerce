=== Smoove abandoned cart trigger for WooCommerce ===
Contributors: matansmoove
Donate link: https://wordpressfoundation.org/donate/
Tags: 4.0.3
Requires at least: 5.5.3
Tested up to: 6.5.3
Stable tag: 4.0.3
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html



Recover abandoned carts in your Woo store by transmitting the shopper’s info to your smoove account.

== Description ==

The plugin allows you to capture all of the abandoned carts in your Woo store and transmit the cart’s shopper data to your Smoove account so you can easily recover those carts.
The plugin “fires” the cart and shopper data to the Smoove platform, where it can be managed, streamlined into an automation or stored for future use.

Start by going to the “Smoove settings”;
Enter an API key from your Smoove platform.
Set the cart lifetime interval to the desired value. 
Set what should be done with shoppers which are already contacts in your Smoove account but are unsubscribed or deleted.

After settings are completed go to the “Plugin Test” page:
Enter “dummy” details of an imaginary contact and click “Test”.
If the settings were done successfully you should see a “Code 200 message OK” and that “dummy” contact will appear in your Smoove account at the “All contacts” list.

In the “All Abandoned Carts” page you can view the history log of previous carts which were abandoned in your store since you have installed the plugin.

How can you find the abandoned cart’s info in your Smoove account? Those details will be transmitted to an “Abandoned cart” trigger which you can add to your automation canvas.
Once the plugin identifies an abandoned cart it will  transmit that data which will arrive in your automation where you can manage it in any way you wish - add the contact to a new list, update fields, send an email, etc.

The data transmitted includes the shopper’s personal info as well as a cart recovery unique URL and the list of abandoned products.

== Installation ==

1. In your Smoove account create an API key.
1. Please make sure that you have installed WooCommerce.
1. Go to plugins in your dashboard and select "Add New".
1. Search for the `Smoove abandoned cart trigger for WooCommerce`, install and activate.
1. Enter the “Smoove settings” in the plugin’s menu and enter the API key and follow the instructions in the description.

== Frequently Asked Questions ==

= The cart data was not received in my Smoove account. =

If the shopper in hand is an unsubscribed contact in your Smoove account it will not be transmitted unless elected otherwise in the plugin settings page.

= How does the plugin know to start the countdown until it considers a cart as abandoned. =

Once an email has been entered in the checkout page then the clock starts. If the purchase was not completed within the cart lifetime then this will be considered as an abandoned cart. 

= The abandoned cart was triggered and the automation started in my Smoove account but the automation did not send an email to the shopper who has abandoned their cart. =

The shopper may be an existing contact in your Smoove account which elected to stop receiving emails and/or sms from you, go into that contact’s details to look for those settings which should 

== Screenshots ==

1. The settings page > enter your API key generated from the Smoove account > set the cart lifetime interval and select restoration settings for deleted or unsubscribed contacts.
2. The plugin test page > enter “dummy” shopper data to test the plugin’s connection with your Smoove account.
3. A successful test will show a “Code: 200, Message: OK”.
4. The Abandoned carts page where you can view the history log.

== Changelog ==
