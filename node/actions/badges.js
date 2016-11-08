var Actions      = require('../lib/actions');
var Triggers     = require('../lib/triggers');
var ParamBuilder = require('../lib/param-builder');
var Messages     = require('./messages');

try {
	var Twitter = require('./twitter');
} catch (err) {
	console.log(err.message);
}

/****************************************************************************
 * Badges
 ****************************************************************************/
var Badges = module.exports = {
	register: function (params) {
		var messageQueue = Messages.init(params.node, 'ESP-BADGE', '/badge', 1, 1, 40);
		if (typeof Twitter == 'object') {
			messageQueue.defaultMsg(
				{
					text: 'Tweet with '+Twitter.tracking().join(' or ')+' to see your message here ;-)',
					r: 1, g: 1, b: 0
				}
			);
		}
	}
};

Actions.
	register(
		'Badges.register', 
		function (params) {
			Badges.register(params);
		},
		'Register message queue for ESP-BADGE device'
	).
	parameter('node',   'string', true)
;

Triggers.
	register(
		{
			id:     null,
			userID: null,
			active: true,
			type: 'onRegisterDevice',
			data: {
				node:       null, 
				deviceURL:  '/badge'
			},
			action: 'Badges.register'
		},
		ParamBuilder().
			parameter({name: 'node',   value: '[NodeToken]'})
	)
;
