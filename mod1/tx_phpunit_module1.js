/*
 * First some ugly JavaScript code (polluting the global namespace)
 */
script_ended = 0;
function jumpToUrl(URL)	{	//
	document.location = URL;
}

function setClass(id, className) {
	// Using YUI! to make function a bit more resilient
	YAHOO.util.Dom.replaceClass(id, 'testcaseSuccess', className);
}

/*
 * This is using YUI! 2.6.0, cf. http://developer.yahoo.com/yui/
 * We can safely use YUI! since PHPUnit includes yahoo-dom-event.js 
 */
(function () {
	YAHOO.namespace('phpunit');

	// Convenience shortcuts
	var Dom = YAHOO.util.Dom,
		Event = YAHOO.util.Event,
		Connect = YAHOO.util.Connection,
		phpunit = YAHOO.phpunit;

	/*
	 * Constructor function for TestRunner instances 
	 */
	phpunit.TestRunner = function () {
		var secret = 55;
		var secretObj = { nr: 10 };
		
		return {
			getSecret: function () { return secret },
			getSecretObj: function () { return secretObj },
			incrementSecret: function (val) { secret += val },
			decrementSecret: function (val) { secret -= val }
		}
	}

	var toggle = function (event) {
		var target = Event.getTarget(event);
		var display = target.checked ? 'block' : 'none';
		var className = mapClasses(target.name);
		var state = target.checked;
		var checkbox;
		switch (target.id) {
		case 'SET[failure]':
			checkbox = 'failure';
			break;
		case 'SET[error]':
			checkbox = 'error';
			break;
		case 'SET[success]':
			checkbox = 'success';
			break;
		default:
			// Nothing here.
			break;
		}
	
		var transaction = YAHOO.util.Connect.asyncRequest('POST', 'ajax.php', 
			{	success: function (responseObj) { console.log('Success', responseObj); },
				failure: function (responseObj) { console.log('Failure', responseObj); }
			},
			'ajaxID=tx_phpunit_module1_ajax&state='+state+'&checkbox='+checkbox
		);
		Dom.setStyle(Dom.getElementsByClassName(className), 'display', display);
	}
	
	var mapClasses = function (buttonId) {
		var className;
		switch (buttonId) {
		case 'SET[success]':
			className = 'testcaseSuccess';
			break;
		case 'SET[failure]':
			className = 'testcaseFailure';
			break;
		case 'SET[error]':
			className = 'testcaseError';
			break;
		default:
			// Yikes!
			break;
		}
		return className;
	}
	
	Event.onDOMReady(function () {
		var toggleButtonsIds = Dom.get(['SET[failure]', 'SET[success]', 'SET[error]']);
		for (var i = 0; i < toggleButtonsIds.length; i += 1) {
			var elm = toggleButtonsIds[i];
			var display = elm.checked ? 'block' : 'none';
			var className = mapClasses(elm.name);
			Dom.setStyle(Dom.getElementsByClassName(className), 'display', display);
		}
		Event.addListener(toggleButtonsIds, 'click', toggle, this, true);
	}, this, true);
})();