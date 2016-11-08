var Actions      = require('../lib/actions');
var Triggers     = require('../lib/triggers');
var ParamBuilder = require('../lib/param-builder');
var Connections  = require('../lib/connections');

/****************************************************************************
 * EMTRInit
 * Set MOD-EMTR read interval
 ****************************************************************************/

function EMTRInit(params) {
	var connection = Connections.findNode(params.userID, params.node);
	if (connection === null) {
		return;
	}
	
	var ref = typeof params.ref == 'undefined' ? 
		null 
		: 
		params.ref
	;
	
	connection.sendUTF(
		JSON.stringify(
			{
				Method: 'POST',
				URL: '/mod-emtr',
				Data: {
					ReadInterval: params.readInterval,
					Filter:       params.filter ? 1 : 0
				},
				ref: ref
			}
		)
	);
}

Actions.
	register('EMTR.init', EMTRInit, 'Set read interval for MOD-EMTR').
	parameter('node',         'string',  true, 'nodeSelector').
	parameter('readInterval', 'number',  true, 'propertySelector').
	parameter('filter',       'boolean', true, 'booleanNoYes')
;
