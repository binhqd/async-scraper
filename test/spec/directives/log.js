'use strict';

describe('Directive: log', function () {

  // load the directive's module
  beforeEach(module('miningApp'));

  var element,
    scope;

  beforeEach(inject(function ($rootScope) {
    scope = $rootScope.$new();
  }));

  it('should make hidden element visible', inject(function ($compile) {
    element = angular.element('<log></log>');
    element = $compile(element)(scope);
    expect(element.text()).toBe('this is the log directive');
  }));
});
