/****************************************************************************
 * Timestamped console.log()
 ****************************************************************************/

var ConsoleLog   = console.log;
var ConsoleError = console.error;

function PrefixConsole(callback, args) {
	function pad(d) {
		if (d < 10) {
			return '0'+d;
		}
		return d;
	}
	
	var now = new Date();
	var time = '['+
		now.getFullYear()+'-'+pad(now.getMonth()+1)+'-'+pad(now.getDate())+' '+
		pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds())+
		']'
	;
	
	if (args.length > 0 && typeof args[0] == 'string') {
		args[0] = '%s '+args[0];
		args.splice(1, 0, time);
	} else {
		args.splice(0, 0, '%s ', time);
	}
	
	callback.apply(console, args);
}

console.log = function () {
	PrefixConsole(ConsoleLog, Array.prototype.slice.call(arguments));
}

console.error = function () {
	PrefixConsole(ConsoleError, Array.prototype.slice.call(arguments));
}

