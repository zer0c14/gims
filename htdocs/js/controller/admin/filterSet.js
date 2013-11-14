/* Controllers */

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/FilterSetCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.filterSets = Restangular.all('filter-set').getList();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 800})],
        data: 'filterSets',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        filterOptions: {},
        multiSelect: false,
        columnDefs: [
            {field: 'name', displayName: 'Name', width: '750px'},
            {displayName: '', cellTemplate: '' +
                        '<button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

    // <button type="button" class="btn btn-default btn-xs" ng-click="edit(row)" ><i class="fa fa-pencil fa-lg"></i></button>
    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.filterSets, label: row.entity.code, returnUrl: '/admin/filter-set'});
    };

//    $scope.edit = function (row) {
//        $location.path('/admin/survey/edit/' + row.entity.id);
//    };

});