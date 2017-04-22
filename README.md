# Telegram-VK-messenger-PHP

You can recieve messages from VK using /get command.
And answer to them by Reply button in Telegram.

To start you have to set your token and webhook_url in config.
Next set up your database user,pass and so on.
In index.php set your client_id accroding to yours VK App ID.
After that you should run bot with /start command.
Use: /start <Your vk ID>
Next get access_token from url and use /token <access_token>

Warning! Only you can recieve and send messages. 
If somebody else use bot they get nothing. Cause it's VK API's trouble 
