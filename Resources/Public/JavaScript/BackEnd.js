'use strict'

/**
 * Caches the already went-through progress bar states to reduce DOM operations.
 * @type {Array}
 */
var stateCache = [];

/**
 * @param {string} id
 * @param {string} className
 */
function setClass(id, className) {
	YAHOO.util.Dom.replaceClass(id, 'testcaseSuccess', className);
}

/**
 * Sets the CSS class of the progress bar.
 *
 * @param {string} className the class name to set, must not be empty
 *
 * @return void
 */
function setProgressBarClass(className) {
	if (stateCache.indexOf(className) === -1) {
		setClass("progress-bar", className);
		stateCache.push(className);
	}
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
	};

	var toggle = function (event) {
		var target = Event.getTarget(event);
		var display = target.checked ? 'block' : 'none';
		var className = mapClasses(target.id);
		var state = target.checked ? "1" : "0";
		var checkbox;
		switch (target.id) {
		case 'SET_failure':
			checkbox = 'failure';
			break;
		case 'SET_error':
			checkbox = 'error';
			break;
		case 'SET_success':
			checkbox = 'success';
			break;
		case 'SET_skipped':
			checkbox = 'skipped';
			break;
		case 'SET_incomplete':
			checkbox = 'incomplete';
			break;
		case 'SET_testdox':
			checkbox = 'testdox';
			break;
		case 'SET_showTime':
			checkbox = 'showTime';
			break;
		case 'SET_runSeleniumTests':
			checkbox = 'runSeleniumTests';
			break;
		default:
		}

		YAHOO.util.Connect.asyncRequest('POST', 'ajax.php',
			{	success: function (responseObj) {},
				failure: function (responseObj) {}
			},
			'ajaxID=Tx_Phpunit_BackEnd_Ajax&state=' + state + '&checkbox=' + checkbox
		);
		toggleStyleNodeForMassHidingOfElements(className, display);
	};

	var toggleCodeCoverage = function (event) {
		var target = Event.getTarget(event);
		var state = target.checked ? "1" : "0";
		YAHOO.util.Connect.asyncRequest('POST', 'ajax.php',
				{	success: function (responseObj) {},
					failure: function (responseObj) {}
				},
				'ajaxID=Tx_Phpunit_BackEnd_Ajax&state=' + state + '&checkbox=codeCoverage'
			);
	};

	/**
	 * Sets class to container which indicates testcases to be hidden/shown
	 * (performance boost compared to iterative adding of inline styles)
	 *
	 * @param {String} className
	 * @param {String} showState
	 */
	var toggleStyleNodeForMassHidingOfElements = function(className, showState) {
		// white-listing of relevant class names
		if (['testcaseSuccess', 'testcaseSkipped', 'testcaseError', 'testcaseFailure'].indexOf(className) === -1) {
			return;
		}
		var containerNode = document.getElementsByTagName('body')[0];
		var containerNodeClassName = ' hide-' + className + ' ';
		// hasClass
		var classNameAlreadySet = (containerNode.className).indexOf(containerNodeClassName) > -1;

		if (showState === 'none' && !classNameAlreadySet) {
			// addClass
			containerNode.className = containerNode.className + containerNodeClassName;
		}
		if (showState !== 'none' && classNameAlreadySet) {
			// removeClass
			containerNode.className = containerNode.className.replace(containerNodeClassName, '');
		}
	};

	/**
	 * Maps a checkbox ID to a class name for the corresponding test results.
	 *
	 * @param {string} buttonId the ID of a checkbox, e.g. "SET_success"
	 *
	 * @return {string} the corresponding class name, e.g. "testcaseSuccess"
	 */
	var mapClasses = function (buttonId) {
		var className;
		switch (buttonId) {
		case 'SET_success':
			className = 'testcaseSuccess';
			break;
		case 'SET_failure':
			className = 'testcaseFailure';
			break;
		case 'SET_error':
			className = 'testcaseError';
			break;
		case 'SET_skipped':
			className = 'testcaseSkipped';
			break;
		case 'SET_incomplete':
			className = 'testcaseIncomplete';
			break;
		case 'SET_testdox':
			className = 'testdox';
			break;
		case 'SET_showTime':
			className = 'showTime';
			break;
		case 'SET_runSeleniumTests':
			className = 'runSeleniumTests';
			break;
		default:
			className = '';
		}
		return className;
	};

	/**
	 * Hides/shows the test results depending on states of the test status
	 * checkboxes. Also adds the JavaScript event handlers to the checkboxes.
	 */
	Event.onDOMReady(function () {
		var checkboxes = Dom.get([
			'SET_failure', 'SET_success', 'SET_error', 'SET_skipped', 'SET_incomplete',
			'SET_testdox', 'SET_showTime', 'SET_runSeleniumTests'
		]);
		var numberOfCheckboxes = checkboxes.length;
		for (var i = 0; i < numberOfCheckboxes; i++) {
			var checkbox = checkboxes[i];
			var display = checkbox.checked ? 'block' : 'none';
			var className = mapClasses(checkbox.id);
			toggleStyleNodeForMassHidingOfElements(className, display);
		}
		Event.addListener(checkboxes, 'click', toggle, this, true);
		Event.addListener('SET_codeCoverage', 'click', toggleCodeCoverage, this, true);

		checkForCrashedTest();
	}, this, true);

	/**
	 * Checks whether the last displayed test has crashed. In that case, un-hides it.
	 *
	 * @return {void}
	 */
	var checkForCrashedTest = function() {
		if ($('testsHaveFinished')) {
			return;
		}

		setProgressBarClass("hadError");

		var testContainers = Dom.getElementsByClassName('testcaseOutput');
		var lastTestContainer = testContainers[testContainers.length - 1];

		var testChildren = Dom.getChildren(lastTestContainer);

		var pre = document.createElement('pre');
		pre.className = 'message';

		var numberOfChildren = testChildren.length;
		for (var i = 0; i < numberOfChildren; i++) {
			var node = testChildren[i];
			if (node.tagName !== 'H3') {
				lastTestContainer.removeChild(node);
				pre.appendChild(node);
			}
		}
		lastTestContainer.appendChild(pre);

		lastTestContainer.className = 'testcaseOutput testcaseError';
		lastTestContainer.style.display = 'block';
	}
})();