/****************************************************************************
 * Clock events generation
 ****************************************************************************/
module.exports = {
	weekDay: [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday"
	],
	
	monthName: [
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December"
	],
	
	pad: function (d) {
		if (d < 10) {
			return '0'+d;
		}
		return d;
	},
	
	time: function (h, m) {
		return this.pad(h) + ':' + this.pad(m);
	},
	
	get: function () {
		var now = new Date();
		
		var time = this.time(now.getHours(), now.getMinutes());
		var dayOfWeek = this.weekDay[now.getDay()];
		var month = this.monthName[now.getMonth()];
		
		return {
			Minutes:       now.getMinutes(),
			Hour:          now.getHours(),
			Day:           dayOfWeek,
			Date:          now.getDate(),
			Month:         month,
			Year:          now.getFullYear(),
			
			Time:          time,
			DayTime:       dayOfWeek + ' ' + time,
			DateTime:      now.getDate() + ' ' + time,
			MonthDateTime: month + ' ' + now.getDate() + ' ' + time
		};
	},
	
	dowFromMask: function (mask) {
		var dow = [];
		for (var i=0; i<8; i++) {
			if (((1 << i) & mask) != 0) {
				dow.push(this.weekDay[i]);
			}
		}
		
		return dow;
	},
	
	run: function () {
		var self = this;
		var Connections = require('./connections');
		
		var fireClockEvent = function () {
			Connections.event(
				{
					EventURL: '/clock',
					EventData: {
						Device: 'CLOCK',
						Status: 'OK',
						Data: {
							Clock: self.get()
						}
					}
				}
			);
		};
		
		// Set initial clock state
		fireClockEvent();
		
		var now = new Date();
		setTimeout(
			function () {
				// first clockEvent 1 second after round minute
				fireClockEvent();
				
				// rest clockEvents each 60 seconds
				setInterval(fireClockEvent, 60000);
			},
			(61 - now.getSeconds()) * 1000
		);
	}
};
