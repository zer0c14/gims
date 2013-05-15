/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, $resource, Question, Filter, Modal, Gui) {
    "use strict";

    Gui.resetSaveButton($scope);

    // Default redirect
    var redirectTo = '/';

    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.cancel = function () {
        $location.path(redirectTo).search({});
    };

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.save = function (redirectTo) {

        $scope.sending = true;

        // First case is for update a question, second is for creating
        $scope.question.filter = $scope.question.filter.id;
        if ($scope.question.id > 0) {
            $scope.question.$update({id: $scope.question.id}, function () {
                Gui.resetSaveButton($scope);

                if (redirectTo) {
                    $location.path(redirectTo).search({});
                }
            });
        } else {
            $scope.question.survey = $routeParams.survey;
            delete $scope.question.sorting; // let the server define the sorting value
            $scope.question.$create(function (question) {

                Gui.resetSaveButton($scope);

                if (redirectTo) {
                    $location.path(redirectTo).search({});
                } else {
                    // redirect to edit URL
                    redirectTo = sprintf('admin/question/edit/%s', question.id);
                    $location.path(redirectTo);

                }
            });
        }
    };

    // Delete a question
    $scope.delete = function () {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };

    // Create object with default value
    $scope.question = new Question({'sorting': 0, 'type': 'foo'});

    // Try loading question if possible...
    if ($routeParams.id > 0) {
        Question.get({id: $routeParams.id}, function (question) {
            $scope.question = new Question(question);
        });
    }

    var formatSelection = function (filter) {
        return filter.name;
    };

    var formatResult = function (filter) {
        return formatSelection(filter);
    };

    var filters;
    Filter.query(function (data) {
        filters = data;
    });

    $scope.availableFilters = {
        query: function (query) {

            var data = {results: []};

            var searchTerm = query.term.toUpperCase();
            var regexp = new RegExp(searchTerm);

            angular.forEach(filters, function (filter) {
                var blob = (filter.id + ' ' + filter.name).toUpperCase();
                if (regexp.test(blob)) {
                    data.results.push(filter);
                }
            });
            query.callback(data);
        },
        formatResult: formatResult,
        formatSelection: formatSelection
    };
});

