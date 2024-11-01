# The WordPress plugin to connect Chatbase API and LINE offical account
The plugin can integrate the AI reply into LINE offical account. You will need to prepare the following six pieces of information:

	1.	Access token for the LINE Messaging API
	2.	Secret for the LINE Messaging API
	3.	Bot ID for Chatbase
	4.	Token for Chatbase
	5.	URL for submitting training data to Chatbase
	6.	Email address that will receive correction notifications

You can refer to this tutorial to obtain the first and second items: https://developers.line.biz/en/docs/basics/channel-access-token/

For the third item, log in to the Chatbase interface, select the specific bot, and retrieve it from its settings page:

![Getting Chatbase bot id](https://oberonlai.blog/wp-content/uploads/wordpress-line-ai-bot/wordpress-line-ai-bot-01.jpg)

The fourth item is to add a new API Key in the account settings interface of Chatbase:

![Getting Chtabase api key](https://oberonlai.blog/wp-content/uploads/wordpress-line-ai-bot/wordpress-line-ai-bot-02.jpg)

The fifth item is a link that will appear in the email notifications. You can link it directly to the source page of Chatbase.

# Setting the API information
After obtaining the above information, open the following file: `wp-content/dwp-line-bot/src/Api.php` and fill in the corresponding attributes with the data mentioned above.

```
private static $channel_access_token = ''; // LINE Messaging API access token.
private static $channel_secret       = ''; // LINE Messaging API secret.
private static $chatbase_bot_id      = ''; // Chatbase bot id.
private static $chatbase_token       = ''; // Chatbase api key.
private static $chatbase_source      = ''; // Chatbase url of providing source.
private static $email                = ''; // The email of receiving bot notifications. 
```

After making the modifications, save and upload the file. Next, set the Webhook of the LINE Official Account to the API path of your website. Go to the LINE Developers console, find Webhook settings, and set the Webhook URL to:

https://yoursite.com/wp-json/dwp/v1/webhook

Replace yoursite.com with your website’s URL. This will allow Chatbase’s response results to be sent directly to LINE.

# Setting the email notification
In line 143 of the Api.php file, there is a condition set to trigger a correction notification email. Currently, I have it set to notify me when the question contains product-related keywords like “line” or “ordernotify,” and when the reply contains AI-generated text. You can modify this according to your needs.

```
if ( strpos( $reply, 'AI-generated' ) !== false || strpos( $question, 'line' ) !== false || strpos( $question, 'ordernotify' ) !== false ) {
  wp_mail( self::$email, 'LINE Bot fix', 'Q：' . $question . ' <br><br>A：' . $reply . '<br><br>Go to fix：' . self::$chatbase_source, array( 'Content-Type : text/html; charset=utf-8' ) );
}
```

