# Prestashop-Resend-Order-Confirmation-Email
### Resend the order confirm email in Prestashop.

I have found it incredibly tedious having to constantly rewrite the code for resending order confirmation emails in Prestashop. 

To make things easier, I have created this module. 

You are welcome to use or even improve upon it. 

- I have only tested it on Prestashop 1.7.8.4, but I believe it should work across the 1.7.x.x versions. 
- I have only tested this on orders with the order status "Payment Accepted". It might work with other order status too. Test it with your own email address before you send a broken email to your customer.
- This module doesnt modify any order data, or any data on your Prestashop database in any meaningful way. That means it won't change order status; it won't modify any products; ~~it won't create a voucher to give me 20% off, probably.~~ :P


### To use this:
1. Install the module, see below if you are not sure how to install a module.
2. Go to an order in the backoffice of Prestashop. 
3. Scroll down to find "Resend Order Confirmation Email"
4. By default, it sends the email to the customer's current email address. You can change it to send it to an alternitive email address.


### To install:
1. Click on the green top right button that says "<> Code",
2. Click on Download ZIP
3. Open the zip file, you should see a folder in it called "Prestashop-Resend-Order-Confirmation-Email-main", go into that folder and you should see another folder named "presta_resend_order_conf_email".
4. Extract that folder out and re-zip it.
5. Go to the back office of Prestashop, On the left hand side navigation bar, find Improve -> Modules -> click on Module manager
6.  Click on "Upload a module", then click on "select a file". Select the zip file you have created in step 4.
7. Ta-da. Done.
