var fs   = require('fs');
var path = require('path');

/****************************************************************************
 * Loader
 ****************************************************************************/
module.exports = function Loader(dir) {
	try {
		dir = path.normalize(dir);
		var exported = {};
		var files = fs.readdirSync(dir);
		for (var i in files) {
			var stats = fs.lstatSync(path.join(dir, files[i]));
			
			if (stats.isDirectory()) {
				var dirExported = Loader(path.join(dir, files[i]));
				for (var e in dirExported) {
					exported[e] = dirExported[e];
				}
				continue;
			}
			
			if (stats.isFile()) {
				var file = files[i].match(/(.+)\.js$/);
				if (file) {
					var name = file[1];
					console.log('LOADING: ' + files[i]);
					exported[name] = require(path.join(dir, files[i]));
				}
			}
		}
		
		return exported;
	} catch (err) {
		console.log('LOADING: '+err);
	}
}