Magento: Two-Factor-Authentication
=====================

----------

Magento Worldwide Online Hackathon, Januar 2014

----------

Implementation of an two-factor-authentication using Google's 2-Step Verification algorithm.

** Abstract **

Admin (backend) users whose role's resources are in the list of protected resources,
are asked to enter one-time security code generated by the Google Authenticator app on their mobile phone after
they have authenticated themselves in the admin by using standard login dialog.
This ensures that critical resources in the admin have extra protection layer that cannot be accessed
by third parties without one-time security code. It includes cases when someone's laptop is stolen or accessed
by third parties.

> **NOTE:**
> Default login will be also required to login!
> 2FA is only an additional login to increase the security.

Todo:
-
- Implement Google's 2FA-Algorithm and One-Time-Passwords
  - Table containing one-time-password's and 2FA-Data
- Disable/Enable for every Admin-User
- Integrate Google QR-Code-Generator
`http://www.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth://totp/idontplaydarts?secret=SECRETVALUEHERE`

** TBD **

Write docs about how to install this extension to  your Magento with composer.

How to use it:
-
- Install Google Authenticator app to your smartphone
- Log in to Magento admin with your existing account and navigate to your profile page under System->My Account
- Scan the QR code under your profile with Google Authenticator app
- Fill in the field 'code' with the generated code from your handset
- Save your profile
- Log out
- Log in again and you should be displayed
