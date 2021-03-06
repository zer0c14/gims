angular.module('myApp.directives').directive('gimsChoiQuestion', function(QuestionAssistant) {
    return {
        restrict: 'E',
        template: "<ng-form name='row show-grid innerQuestionForm'>" +
                "<div class='col-md-12'>" +
                "<table class='show-grid'>" +
                "<tr>" +
                "   <td class='text-center' ng-repeat='part in question.parts' ng-switch='part.name'>" +
                "       <div ng-switch-when='Total'>National</div>" +
                "       <div ng-switch-when='Urban'>Urban</div>" +
                "       <div ng-switch-when='Rural'>Rural</div>" +
                "   </td><td></td>" +
                "</tr>" +
                "<tr ng-repeat='choice in question.choices' >" +
                "   <td class='text-center' ng-repeat='part in question.parts' >" +
                "       <div ng-switch='question.isMultiple'>" +
                "           <div ng-switch-when='true'>" +
                "               <input type='checkbox' ng-disabled='saving' ng-model='index[question.id+\"-\"+choice.id+\"-\"+part.id].isCheckboxChecked' ng-click='save(question,choice,part)' name='{{part.id}}-{{choice.is}}' />" +
                "           </div>" +
                "           <div ng-switch-when='false'>" +
                "               <input type='radio' ng-disabled='saving' ng-required='question.isCompulsory' ng-model='index[question.id+\"-\"+part.id].valueChoice.id' value='{{choice.id}}' ng-click='save(question,choice,part)' name='{{part.id}}-{{question.id}}'/>" +
                "           </div>" +
                "       </div>" +
                "   </td>" +
                "   <td><div style='padding-top:5px'>{{choice.name}}</div></td>" +
                "</tr>" +
                "<tr ng-show='!question.isMultiple'>" +
                "   <td ng-repeat='part in question.parts' class='text-center'>" +
                "       <span class='btn btn-xs btn-danger' ng-click='index[question.id+\"-\"+part.id].valueChoice.id=null; save(question,null,part)'><i class='fa fa-trash-o'></i></span>" +
                "   </td>" +
                "   <td></td>" +
                "</tr>" +
                "</table>" +
                "   <span ng-show='question.isCompulsory' class='badge' ng-class=\"{'badge-danger':question.statusCode==1, 'badge-success':question.statusCode==3}\">Required</span>" +
                "   <span ng-show='!question.isCompulsory' class='badge' ng-class=\"{'badge-warning':question.statusCode==2, 'badge-success':question.statusCode==3}\">Optional</span>" +
                "</div>" +
                "</ng-form>",
        scope: {
            index: '=',
            question: '='
        },
        controller: function($scope, Restangular)
        {
            $scope.saving = false;
            $scope.save = function(question, choice, part)
            {
                $scope.saving = true;
                var identifier;
                if ($scope.question.isMultiple) {
                    identifier = question.id + "-" + choice.id + "-" + part.id;
                } else {
                    identifier = question.id + "-" + part.id;
                }

                var newAnswer = $scope.index[identifier];
                if (choice) {
                    newAnswer.valueChoice = {};
                    newAnswer.valueChoice.id = choice.id;
                    newAnswer.valueChoice.value = choice.value;
                    newAnswer.valuePercent = choice.value;
                }

                // if id setted on radio button, update
                if (newAnswer.id && !$scope.question.isMultiple && choice) {
                    newAnswer.put().then(function() {
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                } else if (newAnswer.id && !$scope.question.isMultiple && !choice) {
                    newAnswer.remove().then(function() {
                        $scope.index[identifier] = QuestionAssistant.getEmptyChoiceAnswer(question, part, null);
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if id is setted on checkbox element, that means that there already is a result and we want to remove it
                } else if (newAnswer.id && $scope.question.isMultiple) {
                    newAnswer.remove().then(function() {
                        $scope.index[identifier] = QuestionAssistant.getEmptyChoiceAnswer(question, part, choice);
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });

                    // if don't exists -> create
                } else if (!newAnswer.id) {
                    Restangular.all('answer').post(newAnswer).then(function(answer)
                    {
                        if ($scope.question.isMultiple) {
                            answer.isCheckboxChecked = true;
                        }
                        $scope.index[identifier] = answer;
                        $scope.saving = false;
                        QuestionAssistant.updateQuestion(question, $scope.index, false, true);
                    });
                }
            };

        }
    };
});
