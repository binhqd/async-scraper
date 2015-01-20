'use strict';

/**
 * @ngdoc directive
 * @name miningApp.directive:log
 * @description # log
 */
app.directive('log', function() {
    return {
        templateUrl: uri + '/templates/log.html',
        scope : false,
        restrict : 'E',
        link : function postLink(scope, element, attrs) {
            //element.text('this is the log directive');
        },
        controller : function($scope) {
            //console.log('asdfas');
        }
    };
});
