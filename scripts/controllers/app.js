var app = angular.module('miningApp', [
	'ngRoute',
	'MiningCtrls',
	'ui.router',
	'ui.bootstrap'
]);

app.config(function($routeProvider, $stateProvider) {

	$stateProvider

	// setup an abstract state for the tabs directive
	.state('mining', {
		url: '/',
		views: {
			'tabContent': {
				templateUrl: uri + '/templates/mining.html',
				controller: 'MiningCtrl'
			}
		}
	})
});
