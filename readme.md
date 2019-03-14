# Test Two-Factor Authentication with Authy, Sentinel and Laravel

You can simply register at `/register` using email address, phone number, name and password. Then authorise by code from Authy app. <br>
Also u can login at `/login` by email and password. Then authorise by code form Authy app. <br>
App contains simple api at `/getUserData` which returns logged user (in JSON) with 200 code or JSON with error and 403 code when noone is logged in.