'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', ['myApp.filters', 'myApp.services', 'myApp.directives']).
        config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
        $routeProvider.when('/home', {templateUrl: '/application/index/home', controller: MyCtrl1});
        $routeProvider.when('/about', {templateUrl: '/application/index/about', controller: MyCtrl2});
        $routeProvider.when('/browse', {templateUrl: '/application/index/browse', controller: MyCtrl2});
        $routeProvider.when('/contribute', {templateUrl: '/application/index/contribute', controller: MyCtrl2});
        $routeProvider.otherwise({redirectTo: '/home'});

        // Enable Zend to recognize ajax requests, so we can serve partial HTML
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        $locationProvider.html5Mode(true);
    }]);