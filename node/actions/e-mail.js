var Actions = require('../lib/actions');
var REST    = require('../lib/rest');

/****************************************************************************
 * eMail
 * Send e-mail message
 ****************************************************************************/

function eMail(params) {
	REST.request(
		{
			host: 'localhost',
			path: '/email',
			post: params
		},
		
		function (status, response) {
			if (status instanceof Error) {
				console.log('eMail: '+String(status));
				return;
			}
			console.log('eMail: '+response);
		}
	);
	
}

Actions.
	register('eMail', eMail, 'Send e-mail message').
	parameter('Subject',  'string',  true, 'propertySelector').
	parameter('Body',     'string',  true, 'propertySelector')
;
