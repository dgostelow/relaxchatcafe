define(['require', 'util/client'], function(require, client) {
	'use strict';

	if(client.browser === 'chrome' || (client.os === 'Windows' && !client.browser === 'firefox')) return require(['plugin/smoothscroll-chrome']);
	// if(client.os === 'MacOS' && client.browser === 'firefox') return require(['plugin/smoothscroll-jquery']);

});