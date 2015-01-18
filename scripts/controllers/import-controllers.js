var MiningCtrls = angular.module('MiningCtrls', []);

function MiningLog(options) {
    this.title = options.title;
    this.data = null;
    this.lines = [];
    this.finished = false;
    
    this.finish = function() {
        this.finished = true;
    }
}
MiningCtrls.controller('MiningCtrl', function($scope, $rootScope, $http,
    $stateParams, Mining) {
    $scope.part = '';
    if (!!$stateParams.part) {
        $scope.part = $stateParams.part;
    }

    $scope.global = {
        logs : [],
        errors : [],
        countries : [],
        cachedCountries : [],
        country : {},
        states : []
    };
    
    var req = {
        method : 'GET',
        url : './api.php?round=sqlGetStates'
    }

    $scope.state = {
    // gettingChapter : false
    };

    $scope.done = false;
    
    $scope.global.logs.push(new MiningLog({
        "title" : "Getting list of states"
    }));
    
    $http(req).success(function(res) {
        var log = $scope.global.logs[0];
        log.title = 'List of states';
        
        log.lines.push({content: res.length + " states found"});
        log.finish();

        // Processing round 2
        log = new MiningLog({
            "title" : "Importing cities"
        });
        
        // save cache for later use
        // $scope.global.cachedCountries = angular.copy(res.items);
        
        $scope.global.states = res;

        $scope.global.logs.push(log);
        $scope.global.round2_remaining = $scope.global.states.length;
        
        // Open 5 threads
        $scope.insertCities(log);
        $scope.insertCities(log);
        $scope.insertCities(log);
        $scope.insertCities(log);
        $scope.insertCities(log);

    }).error(function(xhr) {
        console.log(xhr);
    });

    $scope.insertCities = function(log, callback) {
        if ($scope.global.states.length > 0) {
            
            var state = $scope.global.states[0];
            $scope.global.states.shift();
            
            var req = {
                method : 'GET',
                url : './api.php?round=importCities&stateData=' + encodeURIComponent(JSON.stringify(state))
            }
            
            var line = {content: state.stateName + ' of ' + state.countryName +': importing ...', done : false};
            log.lines.push(line);
            
            $http(req).success(function(res) {
                $scope.global.round2_remaining--;
                
                // continue crawling
                line.content = state.stateName + ' of ' + state.countryName +": cities imported";
                line.done = true;
                
                if ($scope.global.round2_remaining == 0) {
                    log.finish();
                    
                    // processing parse states
                    
                    
                } else {
                    $scope.insertCities(log);
                }
            }).error(function(xhr) {
                console.log(xhr);
                log.errors.push("Error crawling " + country.text);
            });
        } else {
            //stateLog.finish();
            
            // crawling cities of states
        }
    }
});