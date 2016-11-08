var TwitterStream = require('node-tweet-stream');
var Triggers      = require('../lib/triggers');
var Properties    = require('../lib/properties');

// FIXME - use configuration instead of hardcoded values
var TRACK = [
	'#ESP8266_BADGE'
];

// FIXME - use configuration instead of  environment
if (typeof process.env.TWITTER_CONSUMER_KEY == 'undefined') {
	throw new Error('TWITTER ERROR: Missing TWITTER_CONSUMER_KEY environment variable');
}

if (typeof process.env.TWITTER_CONSUMER_SECRET == 'undefined') {
	throw new Error('TWITTER ERROR: Missing TWITTER_CONSUMER_SECRET environment variable');
}

if (typeof process.env.TWITTER_TOKEN == 'undefined') {
	throw new Error('TWITTER ERROR: Missing TWITTER_TOKEN environment variable');
}

if (typeof process.env.TWITTER_TOKEN_SECRET == 'undefined') {
	throw new Error('TWITTER ERROR: Missing TWITTER_TOKEN_SECRET environment variable');
}

// Init Twitter Stream 
var Twitter = new TwitterStream(
	{
		consumer_key    : process.env.TWITTER_CONSUMER_KEY,
		consumer_secret : process.env.TWITTER_CONSUMER_SECRET,
		token           : process.env.TWITTER_TOKEN,
		token_secret    : process.env.TWITTER_TOKEN_SECRET
	}
);

// Handle tweets
Twitter.on(
	'tweet', 
	function (tweet) {
		console.log('TWEET: ['+tweet.user.name+'] '+tweet.text+' \n');
		Connections.event(
			{
				EventURL: '/tweet',
				EventData: {
					Device: 'TWITTER',
					Status: 'OK',
					Data: {
						Twitter: {
							User: tweet.user.name,
							Text: tweet.text
						}
					}
				}
			}
		);
	}
);

// Handle Twitter errors
Twitter.on(
	'error', 
	function (err) {
		console.log('TWEET ERROR: '+err+'\n');
	}
);

// Init tracks
for (i in TRACK) {
	Twitter.track(TRACK[i]);
}

Properties.
	register(null, 'TweetUser', '$.EventData.Data.Twitter.User').
	register(null, 'TweetText', '$.EventData.Data.Twitter.Text')
;

module.exports = Twitter;
