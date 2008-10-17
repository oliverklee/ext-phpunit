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
 * This is Prototype (shipped with Typo3 Core)
 */
(function () {
	// Using the Typo3 4.2 AJAX approach.
	var tx_phpunit_module1 = {
		thisScript: 'ajax.php',
		ajaxID: 'tx_phpunit_module1_ajax',
		load: function(event) {
			var target = event.element();
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
				// Yikes!
				break;
			}
			new Ajax.Request('ajax.php', {
				method: 'post',
				parameters: 'ajaxID=tx_phpunit_module1_ajax&state='+state+'&checkbox='+checkbox,
				onComplete: function(xhr) { console.log(xhr.responseText); }.bind(this),
				onT3Error: function(xhr) { 
					// if this is not a valid ajax response, the whole page gets refreshed
					console.log(xhr.responseText);
				}.bind(this)
			});
		}
	};

	document.observe("dom:loaded", function() {
		p = $$('input[type="checkbox"]');
		for (var i = 0; i < p.length; i += 1) {
			p[i].observe('click', tx_phpunit_module1.load);
		}
	});
})();

/*
 * This is using YUI! 2.6.0, cf. http://developer.yahoo.com/yui/
 * We can safely use YUI! since PHPUnit includes yahoo-dom-event.js 
 */
(function () {
	var Dom = YAHOO.util.Dom,
		Event = YAHOO.util.Event;

	var toggle = function (event) {
		var target = Event.getTarget(event);
		var display = target.checked ? 'block' : 'none';
		var className = mapClasses(target.name);
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
		var toggleButtonsIds = ['SET[failure]', 'SET[success]', 'SET[error]'];
		for (var i = 0; i < toggleButtonsIds.length; i += 1) {
			var elm = document.getElementById(toggleButtonsIds[i]);
			var display = elm.checked ? 'block' : 'none';
			var className = mapClasses(elm.name);
			Dom.setStyle(Dom.getElementsByClassName(className), 'display', display);
		}
		Event.addListener(toggleButtonsIds, 'click', toggle, this, true);
	}, this, true);
})();